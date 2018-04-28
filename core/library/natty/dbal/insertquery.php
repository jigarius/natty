<?php

namespace Natty\DBAL;

defined('NATTY') or die;

/**
 * Insert Query 
 * @author JigaR Mehta | Greenpill Productions
 */
class InsertQuery
extends QueryBuilder {
    
    protected $type = 'INSERT';

    public function into($tablename) {
        return $this->addTable($tablename);
    }
    
}