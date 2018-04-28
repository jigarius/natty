<?php

namespace Natty\DBAL;

/**
 * Delete Query
 * @author JigaR Mehta | Greenpill Productions
 */
class DeleteQuery extends QueryBuilder {
    
    protected $type = 'DELETE';
    
    public function addColumns(array $fields, $tablename = null) {
        throw new \BadMethodCallException('Call to ' . __METHOD__ . ' not allowed!');
    }
    
    public function addColumn( $field, $tablename = null ) {
        throw new \BadMethodCallException('Call to ' . __METHOD__ . ' not allowed!');
    }
    
    /**
     * The table to delete records from.
     * @param string $table Table name
     */
    public function from($table) {
        return $this->addTable($table);
    }
    
}