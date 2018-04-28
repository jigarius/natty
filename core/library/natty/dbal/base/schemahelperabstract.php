<?php

namespace Natty\DBAL\Base;

use Natty\Helper\FileCacheHelper;

abstract class SchemaHelperAbstract
implements SchemaHelperInterface {
    
    /**
     * Associated database connection
     * @var \Natty\DBAL\Base\Connection
     */
    protected $connection;
    
    /**
     * Static cache for schema data
     * @var array
     */
    protected $cache;
    
    final public function __construct( $connection ) {
        if ( !is_a($connection, 'Natty\\DBAL\\Base\\Connection') )
            trigger_error('Argument 1 expected to be a Database Connection Object!', E_USER_ERROR);
        $this->connection = $connection;
    }
    
    public function readTable( $tablename, $cache = TRUE ) {
        
        if ( !isset ($this->cache[$tablename]) ):
            $cache_key = $this->connection->getId() . ':' . $tablename;
            if ( !$definition = FileCacheHelper::read('system/schema', $cache_key) ):
                // Read actual table definition
                if ( $definition = $this->readTable($tablename, FALSE) )
                    FileCacheHelper::write('system/schema', $cache_key, $definition);
            endif;
            $this->cache[$tablename] =& $definition;
        endif;
        
        return $this->cache[$tablename];
        
    }
    
    final public function readColumn($tablename, $columnname, $cache = true) {
        if ( !$definition = $this->readTable($tablename, $cache) )
            trigger_error('Schema definition not found for object "' . $tablename . '"');
        if ( !isset ($definition['columns'][$columnname]) )
            return FALSE;
        return $definition['columns'][$columnname];
    }
    
    protected static function touchColumnDefinition( array &$definition ) {
        
        // See if the definition has been touched
        if ( isset ($definition['touched']) )
            return;
        
        // Merge with defaults
        $definition = array_merge(array (
            'name' => FALSE,
            'type' => FALSE,
            'description' => FALSE,
            'length' => FALSE,
            'flags' => array (),
        ), $definition);
        
        // Verify index name
        if ( !$definition['name'] )
            throw new \InvalidArgumentException('Column definition missing name!', E_USER_ERROR);
        
        // Determine default lengths
        switch ( $definition['type'] ):
            case 'int':
                $definition['length'] = $definition['length'] 
                    ? $definition['length'] : 10;
                break;
            case 'char':
            case 'varchar':
                $definition['length'] = $definition['length'] 
                    ? $definition['length'] : 255;
                break;
            case 'date':
            case 'time':
            case 'datetime':
                break;
            case 'float':
                $definition['length'] = $definition['length'] 
                    ? $definition['length'] : '10,2';
                break;
            case 'text':
            case 'blob':
                $definition['length'] = in_array($definition['length'], array ('tiny', 'medium', 'long'))
                        ? $definition['length'] : '';
                break;
        endswitch;
        
        // Nullable?
        if (array_key_exists('default', $definition) && is_null($definition['default']) )
            $definition['flags'][] = 'nullable';
        
        ksort($definition);
        $definition['touched'] = true;
        
    }
    
    protected static function touchTableDefinition( &$definition ) {
        
        // See if the definition has been touched
        if ( isset ($definition['touched']) )
            return;
        
        // Merge with defaults
        $definition = array_merge(array (
            'description' => false,
            'columns' => array (),
            'indexes' => array (),
        ), $definition);
        
        // Verify table name
        if ( !$definition['name'] )
            throw new \InvalidArgumentException('Table definition missing name!', E_USER_ERROR);
        
        // Preprocess column definitions
        foreach ( $definition['columns'] as $columnname => &$column_definition ):
            $column_definition['name'] = $columnname;
            self::touchColumnDefinition($column_definition);
            unset ($column_definition);
        endforeach;
        
        // Preprocess index definitions
        foreach ( $definition['indexes'] as $indexname => &$index_definition ):
            $index_definition['name'] = $indexname;
            self::touchIndexDefinition($index_definition);
            unset ($index_definition);
        endforeach;
        
        
        ksort($definition);
        $definition['touched'] = true;
        
    }
    
    protected function uncache( $tablename ) {
        $cache_key = $this->connection->getId() . '/' . $tablename;
        FileCacheHelper::delete('system/schema', $cache_key);
        unset ($this->cache[$tablename]);
    }
    
    final public function readIndex( $tablename, $indexname, $cache = true ) {
        if ( !$definition = $this->readTable($tablename, $cache) )
            trigger_error('Schema definition not found for object "' . $tablename . '"');
        if ( !isset ($definition['indexes'][$indexname]) )
            return false;
        return $definition['indexes'][$indexname];
    }
    
    protected static function touchIndexDefinition( &$definition ) {
        
        // See if the definition has been touched
        if ( isset ($definition['touched']) )
            return;
        
        // Merge with defaults
        $definition = array_merge(array (
            'name' => false,
            'unique' => 0,
            'columns' => array (),
        ), $definition);
        
        // Verify index name
        if ( !$definition['name'] )
            throw new \InvalidArgumentException('Index definition missing name!', E_USER_ERROR);
        
        // Primary index?
        if ( 'PRIMARY' == strtoupper($definition['name']) )
            $definition['unique'] = true;
        
        // Must specify at least one column
        if ( 0 == sizeof($definition['columns']) )
            throw new \InvalidArgumentException('Index definition missing columns!', E_USER_ERROR);
        
        ksort($definition);
        $definition['touched'] = true;
        
    }
    
}