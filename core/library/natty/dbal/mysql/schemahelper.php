<?php

namespace Natty\DBAL\Mysql;

class SchemaHelper
extends \Natty\DBAL\Base\SchemaHelperAbstract {
    
    public function readDatabase( $in_detail = false ) {}
    
    public function createTable( array $definition ) {
        
        // Pre-process definition
        parent::touchTableDefinition($definition);
        
        // See if fields were defined
        if ( !$definition['columns'] )
            trigger_error('Table definition missing columns!', E_USER_ERROR);
        
        // Generate query
        $query = 'CREATE TABLE ';
        $query .= '`' . $definition['name'] . '` (';
        
        // Add field definitions
        $column_i = 0;
        foreach ( $definition['columns'] as $key => $column ):
            
            $column['name'] = $key;
            $query .= "\n\t" . $this->renderColumnQuery($column) 
                    . ( $column_i < (sizeof($definition['columns'])-1) ? ',' : '' );
            
            $column_i++;
            
        endforeach;
        
        // Define primary key, if any. This must be defined here instead of 
        // createIndex - otherwise, auto increment columns would raise an error.
        if ( isset ($definition['indexes']['primary']) )
            $query .= ",\n\tPRIMARY KEY (`" . implode('`, `', $definition['indexes']['primary']['columns']) . "`)";
        
        $query .= "\n)";
        
        // Attempt to execute
        $this->connection->exec($query);

        // Create indexes
        foreach ($definition['indexes'] as $indexname => $index ):
            // Primary key has already been created
            if ( 'primary' == $indexname )
                continue;
            $index['name'] = $indexname;
            $this->createIndex($definition['name'], $index);
        endforeach;
        
    }
    
    public function readTable( $tablename, $cache = TRUE ) {
        
        // The abstract will handle cached requests
        if ( $cache )
            return parent::readTable($tablename);
        
        $dbname = $this->connection->getDbName();
        
        // Derive table information
        $query = "SELECT `TABLE_COMMENT` `description`"
                ." FROM `INFORMATION_SCHEMA`.`TABLES`"
                ." WHERE `TABLE_SCHEMA` = '{$dbname}'"
                    . " AND `TABLE_NAME` = '{$tablename}'";
        $definition = $this->connection->query($query)->fetch(\PDO::FETCH_ASSOC);
        if ( !$definition )
            return FALSE;
        
        // Derive column information
        $query = "SELECT"
                    ." `COLUMN_COMMENT` `description`,"
                    ." `DATA_TYPE` `type`,"
                    ." `COLUMN_NAME` `name`,"
                    ." `NUMERIC_PRECISION` `size`,"
                    ." `IS_NULLABLE` `nullable`,"
                    ." `COLUMN_DEFAULT` `default`,"
                    ." IF (`EXTRA` LIKE '%auto_increment%', 1, 0) `auto`,"
                    ." IF (`COLUMN_TYPE` LIKE '%unsigned%', 1, 0) `unsigned`"
                ." FROM `INFORMATION_SCHEMA`.`COLUMNS`"
                ." WHERE `TABLE_SCHEMA` = '{$dbname}'"
                    ." AND `TABLE_NAME` = '{$tablename}'"
                ." ORDER BY `ORDINAL_POSITION`";
        $column_records = $this->connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $columns = array ();
        foreach ( $column_records as $column ):
            
            $columnname = $column['name'];
            
            switch ( $column['type'] ):
                case 'integer':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $column['type'] = 'int';
                    break;
                case 'tinyblob':
                case 'mediumblob':
                case 'longblob':
                    $column['length'] = substr($column['type'], 0, strlen($column['type']-5));
                    $column['type'] = substr($column['type'], -4);
                    natty_debug($column);
                case 'blob':
                case 'text':
                case 'varchar':
                case 'float':
                case 'decimal':
                case 'int':
                case 'char':
                    break;
                default:
                    throw new \PDOException('Unsupported column type "' . $column['type'] . '" found in table "' . $tablename . '"');
            endswitch;
            
            // Determine flags
            $column['flags'] = array ();
            if ( $column['auto'] )
                $column['flags'][] = 'auto';
            if ( $column['unsigned'] )
                $column['flags'][] = 'unsigned';
            if ( $column['nullable'] = 'YES')
                $column['flags'][] = 'nullable';
            
            unset ($column['name'], $column['auto'], $column['nullable'], $column['unsigned']);
            
            $columns[$columnname] = $column;
            
        endforeach;
        $definition['columns'] = $columns;
        
        // Derive index information
        $query = "SHOW INDEX FROM `{$tablename}`";
        $index_records = $this->connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $indexes = array ();
        foreach ( $index_records as $part ):
            $part['Key_name'] = strtolower($part['Key_name']);
            if ( !isset ($indexes[$part['Key_name']]) )
                $indexes[$part['Key_name']] = array ();
            $index =& $indexes[$part['Key_name']];
            
            if ( !isset ($index['columns']) )
                $index['columns'] = array ();
            $index['columns'][$part['Seq_in_index']-1] = $part['Column_name'];
            if ( !$part['Non_unique'] )
                $index['unique'] = 1;
            
            unset ($index);
        endforeach;
        $definition['indexes'] = $indexes;
        
        return $definition;
        
    }
    
    public function renameTable( $tablename, $newname ) {
        $query = "RENAME TABLE {$tablename} TO {$newname}";
        $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
    public function truncateTable($tablename) {
        $query = 'TRUNCATE {' . $tablename . '}';
        $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
    public function dropTable($tablename) {
        $query = 'DROP TABLE IF EXISTS {' . $tablename . '}';
        $status = $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
    public function createColumn ( $tablename, array $definition ) {
        $this->touchColumnDefinition($definition);
        $query = 'ALTER TABLE {' . $tablename . '}'
                . ' ADD ' . $this->renderColumnQuery($definition);
        if ( !isset ($definition['after']) ) {
            
        }
        elseif (FALSE !== $definition['after']) {
            $query .= ' AFTER {' . $definition['after'] . '}';
        }
        else {
            $query .= ' FIRST';
        }
        $this->connection->exec($query);
    }
    
    public function dropColumn( $tablename, $columnname ) {
        $query = 'ALTER TABLE {' . $tablename . '}'
                . ' DROP {' . $columnname . '}';
        $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
    public function alterColumn( $tablename, $columnname, array $definition ) {
        $query = 'ALTER TABLE {' . $tablename . '}'
                . ' CHANGE {' . $columnname . '} '
                . $this->renderColumnQuery($definition);
        $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
    protected function renderColumnQuery( array $definition ) {
        
        self::touchColumnDefinition($definition);
        extract($definition);
        
        // Generate query
        $output = '{' . $name . '} ';
        
        switch ( $type ):
            case 'int':
                
                // Determine datatype
                $length = $length ? : 10;
                if ( $length <= 2 )
                    $datatype = 'TINYINT';
                elseif ( $length <= 10 )
                    $datatype = 'INT';
                else
                    $datatype = 'BIGINT';
                
                $output .= $datatype . '(' . ($length ? $length : 0) . ')';
                
                // Unsigned?
                $output .= ( in_array('unsigned', $flags) )
                    ? ' UNSIGNED' : ' SIGNED';
                
                // Auto increment?
                if ( in_array('increment', $flags) )
                    $output .= ' AUTO_INCREMENT';
                
                break;
            case 'char':
            case 'varchar':
            case 'float':
            case 'decimal':
                $output .= strtoupper($type) . '(' . $length . ')';
                break;
            case 'date':
            case 'time':
            case 'datetime':
            case 'blob':
            case 'text':
                $output .= strtoupper($length . $type);
                break;
            case 'timestamp':
                $output .= 'INT(16)';
                break;
            default:
                trigger_error('Invalid column type in column definition "' . $type . '"!', E_USER_ERROR);
                break;
        endswitch;
        
        // Nullable?
        $output .= in_array('nullable', $flags)
                ? ' NULL' : ' NOT NULL';
        
        // Default value?
        if ( array_key_exists('default', $definition) ):
            switch ( gettype($definition['default']) ):
                case 'NULL':
                    $output .= ' DEFAULT NULL';
                    break;
                default:
                    $output .= ' DEFAULT ' . $this->connection->quote($definition['default']);
                    break;
            endswitch;
        endif;
        
        // Column comment
        if ( $description )
            $output .= ' COMMENT ' . $this->connection->quote($description);
        
        return $output;
        
    }
    
    public function createIndex( $tablename, array $definition ) {
        
        self::touchIndexDefinition($definition);
        
        if ( $definition['unique'] ) {
            if ( 'primary' == $definition['name'] ) {
                $query = "ALTER TABLE `{$tablename}` ADD PRIMARY KEY (`" . implode('`, `', $definition['columns']) . "`)";
            }
            else {
                $query = "ALTER TABLE `{$tablename}` ADD UNIQUE `{$definition['name']}` (`" . implode('`, `', $definition['columns']) . "`)";
            }
        }
        else {
            $query = "ALTER TABLE `{$tablename}` ADD INDEX `{$definition['name']}` (`" . implode('`, `', $definition['columns']) . "`)";
        }
        
        $this->connection->exec($query);
        $this->uncache($tablename);
        
    }
    
    public function dropIndex( $tablename, $indexname ) {
        if ( 'primary' == $indexname ) {
            $query = "ALTER TABLE `{$tablename}` DROP PRIMARY KEY";
        }
        else {
            $query = "ALTER TABLE `{$tablename}` DROP INDEX `{$indexname}`";
        }
        $this->connection->exec($query);
        $this->uncache($tablename);
    }
    
}