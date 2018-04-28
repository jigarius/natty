<?php

namespace Natty\DBAL;

defined('NATTY') or die;

/**
 * Select Query
 * @author JigaR Mehta | Greenpill Productions
 */
class SelectQuery
extends QueryBuilder {
    
    /**
     * GROUP BY field names
     * @var array 
     */
    protected $grouping = array();
    
    /**
     * JOIN data for SELECT statements
     * @var array
     */
    protected $joins = array();
    
    protected $type = 'SELECT';
    
    /**
     * Adds a table to select data from
     * @param string $table Table name
     * @param string $alias [optional] An alias for the table
     * @return SelectQuery
     */
    public function from($table, $alias = null) {
        return parent::table($table, $alias);
    }

    /**
     * Adds a GROUP BY clause to the query
     * @param array $fields The fields to group by
     * @return SelectQuery
     */
    public function groupBy($fields) {
        $this->grouping = array_merge($this->grouping, $fields);
        return $this;
    }
    
    /**
     * Adds a JOIN clause to the Query
     * @param string $type Type of join: INNER, LEFT or RIGHT
     * @param string $tablename The table to join
     * @param string $condition [optional] The ON clause;
     * @return SelectQuery
     */
    public function addJoin($type, $tablename, $condition = NULL) {
        
        $type = strtoupper($type);
        
        if ( !in_array($type, array ('INNER', 'LEFT', 'RIGHT')) )
            throw new \InvalidArgumentException('Join type must be one of inner, left and right');
        $condition = natty_vod($condition, '1=1');
        
        $entry = array ($type, $tablename, $condition);
        
        array_push($this->joins, $entry);
        return $this;
        
    }
    
}