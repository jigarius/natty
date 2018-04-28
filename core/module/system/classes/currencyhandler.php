<?php

namespace Module\System\Classes;

class CurrencyHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'system--currency',
            'tableName' => '%__system_currency',
            'modelName' => array ('currency', 'currencies'),
            'keys' => array (
                'id' => 'cid',
                'label' => 'nativeName',
            ),
            'properties' => array (
                'cid' => array (),
                'nativeName' => array (),
                'xRate' => array ('default' => 1),
                'unitSymbol' => array ('sdata' => 1, 'default' => ''),
                'unitFirst' => array ('sdata' => 1, 'default' => 1),
                'unitSpacing' => array ('sdata' => 1, 'default' => 1),
                'decimalPlaces' => array ('sdata' => 1, 'default' => 2),
                'decimalSymbol' => array ('sdata' => 1, 'default' => '.'),
                'thouSeparator' => array ('sdata' => 1, 'default' => ','),
                'status' => array ('default' => 1),
            )
        );
        
        parent::__construct($options);
        
    }
    
}