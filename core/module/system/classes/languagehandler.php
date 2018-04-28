<?php

namespace Module\System\Classes;

class LanguageHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'system--language',
            'tableName' => '%__system_language',
            'modelName' => array ('language', 'languages'),
            'keys' => array (
                'id' => 'lid',
                'label' => 'nativeName',
            ),
            'properties' => array (
                'lid' => array (),
                'nativeName' => array (),
                'status' => array ('default' => 1),
            )
        );
        
        parent::__construct($options);
        
    }
    
}