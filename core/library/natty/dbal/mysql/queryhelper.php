<?php

namespace Natty\DBAL\Mysql;

class QueryHelper extends \Natty\DBAL\Base\QueryHelperAbstract {
    
    const ENTITY_QUOTE = '`';
    
    const ENTITY_UNQUOTE = '`';
    
    public function renderDeleteQuery($data) {
        
        extract($data);
        
        $query = 'DELETE';
        
        if ( sizeof($tables) != 1 )
            throw new \Natty\DBAL\QueryException('Missing tablename');
        
        $table = array_shift($tables);
        $query .= " FROM " . $this->quoteObject($table);
        
        $conditions = $this->renderConditions($conditions);
        if ( !$conditions )
            throw new \Natty\DBAL\QueryException('Missing conditions');
        
        $query .= "\nWHERE " . $conditions;
        
        if ( $limit ):
            $offset = $offset ? : 0;
            $query .= "\nLIMIT {$offset}, {$limit}";
        endif;
        
        return $query;
        
    }
    
    public function renderCustomQuery( $query ) {
        return $query;
    }
    
    public function renderSelectQuery($data) {
        
        extract($data);
        
        $query = 'SELECT';
        
        // Distinct records?
        if ( isset ($flags['distinct']) )
            $query .= ' DISTINCT';
        
        $selection = array ();
        
        foreach ( $columns as $key => $t_column ):
            $selection[] = $this->quoteObject($t_column);
        endforeach;
        
        foreach ( $expressions as $key => $t_expression ):
            $selection[] = $t_expression;
        endforeach;
        
        // Select all columns
        if ( 0 === sizeof($selection) )
            $query .= ' *';
        else
            $query .= "\n" . implode(",\n", $selection);
        
        if ( !$tables )
            throw new \Natty\DBAL\QueryException('Missing tablename');
        
        $query .= "\nFROM";
        foreach ( $tables as $key => $t_table ):
            if ( $key > 0 )
                $query .= ',';
            $query .= "\n" . $this->quoteObject($t_table);
        endforeach;
        
        foreach ( $joins as $t_join ):
            list ($type, $table, $condition) = $t_join;
            $condition = $this->renderConditions($condition) ? : '1=1';
            $query .= "\n" . strtoupper($type) . ' JOIN ' . $this->quoteObject($table)
                    . " ON " . $condition;
        endforeach;
        
        $query .= "\nWHERE\n";
        $query .= ( $conditions )
                ? $this->renderConditions($conditions) : '1=1';
        
        // Grouping
        if ( $grouping ):
            $query .= "\nGROUP BY";
            foreach ( $grouping as $key => $column ):
                if ( $key > 0 )
                    $query .= ',';
                $query .= "\n" . $this->quoteObject($column);
            endforeach;
        endif;
        
        // Ordering
        if ( $ordering ):
            $query .= "\nORDER BY";
            foreach ( $ordering as $key => $entry ):
                
                list ($column, $order) = $entry;
            
                if ($key > 0)
                    $query .= ',';
                $query .= "\n";
            
                switch ($order):
                    case 'desc':
                        $query .= $this->quoteObject($column) . ' ' . $order;
                        break;
                    case 'rand':
                        $query .= 'RAND()';
                        break;
                    case 'asc':
                    default:
                        $query .= $this->quoteObject($column) . ' ' . $order;
                        break;
                endswitch;
                
            endforeach;
        endif;
        
        if ( $limit ):
            $offset = $offset ? : 0;
            $query .= "\nLIMIT {$offset}, {$limit}";
        endif;
        
        return $query;
        
    }
    
    public function renderInsertQuery( $data ) {
        
        extract($data);
        
        $query = 'INSERT INTO ';
        
        if ( 1 != sizeof($tables) )
            throw new \Natty\DBAL\QueryException('Missing tablename');
        
        $table = array_shift($tables);
        $query .= $this->quoteObject($table);
        
        // If no columns or values were specified there is an error
        if ( !$columns && !$values )
            throw new \Natty\DBAL\QueryException('Missing columnnames');
        
        // Were the column names specified?
        if ( $columns ):
            $query .= "\n(";
            foreach ( $columns as $key => $t_column ):
                if ( $key )
                    $query .= ', ';
                $query .= $this->quoteObject($t_column);
            endforeach;
            $query .= ')';
        endif;
        
        $query .= "\nVALUES";
        
        $query .= "\n(";
        foreach ( $columns as $key => $t_column ):
            if ( $key )
                $query .= ', ';
            $query .= ':' . $t_column;
        endforeach;
        $query .= ')';
        
        // Conditional insertion?
        if ( $conditions ):
            $conditions = $this->renderConditions($conditions);
            $query .= "\nWHERE " . $conditions;
        endif;
        
        return $query;
        
    }
    
    public function renderUpdateQuery( $data ) {
        
        extract($data);
        
        $query = 'UPDATE ';
        
        if ( 1 != sizeof($tables) )
            throw new \Natty\DBAL\QueryException('Missing tablename');
        
        $table = array_shift($tables);
        $query .= $this->quoteObject($table);
        
        // If no columns or values were specified there is an error
        if ( !$columns )
            throw new \Natty\DBAL\QueryException('Missing columnnames');
        
        // Were the column names specified?
        if ( $columns ):
            $query .= "\nSET ";
            foreach ( $columns as $key => $t_column ):
                if ( $key )
                    $query .= ",\n";
                $query .= $this->quoteObject($t_column) . ' = :' . $t_column;
            endforeach;
        endif;
        
        // Conditional insertion?
        if ( $conditions ):
            $conditions = $this->renderConditions($conditions);
            $query .= "\nWHERE " . $conditions;
        endif;
        
        // Render offset and limit
        if ( $limit ):
            $offset = $offset ? : 0;
            $query .= "\nLIMIT {$offset}, {$limit}";
        endif;
        
        return $query;
        
    }
    
}