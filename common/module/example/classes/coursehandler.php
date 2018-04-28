<?php

namespace Module\Example\Classes;

class CourseHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        $options = array_merge(array (
            'tableName' => '%__example_course',
            'etid' => 'example--course',
            'singularName' => 'course',
            'pluralName' => 'courses',
            'keys' => array (
                'id' => 'cid',
                'label' => 'name'
            ),
            'properties' => array (
                'cid' => array (),
                'code' => array (),
                'name' => array ( 'isTranslatable' => true ),
                'description' => array ( 'isTranslatable' => true ),
                'status' => array (),
            )
        ), $options);
        return parent::__construct($options);
    }
    
}