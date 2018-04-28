<?php

namespace Module\Eav\Classes;

class DatatypeHandler 
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct() {
        parent::__construct(array (
            'tableName' => '%__eav_datatype',
            'etid' => 'eav--datatype',
            'keys' => array (
                'id' => 'dtid',
                'label' => 'name',
            ),
            'singularName' => 'data type',
            'pluralName' => 'data types',
            'properties' => array (
                'dtid' => array (),
                'name' => array (),
                'description' => array ('default' => NULL),
            )
        ));
    }
    
}