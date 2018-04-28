<?php

namespace Module\Example\Classes;

class StudentHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        $options = array_merge(array (
            'tableName' => '%__example_student',
            'etid' => 'example--student',
            'entityObjectClass' => 'Module\\Example\\Classes\\StudentObject',
            'singularName' => 'student',
            'pluralName' => 'students',
            'keys' => array (
                'id' => 'sid',
                'label' => 'name'
            ),
            'properties' => array (
                'sid' => array (),
                'name' => array (),
                'description' => array (),
                'tsCreated' => array (),
                'tsModified' => array (),
                'status' => array (),
            )
        ), $options);
        return parent::__construct($options);
    }
    
}