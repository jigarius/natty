<?php

namespace Module\Commerce\Classes;

class OrderitemHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--orderitem',
            'tableName' => '%__commerce_orderitem',
            'modelName' => array ('order item', 'order items'),
            'keys' => array (
                'id' => 'oiid',
            ),
            'properties' => array (
                'oiid' => array (),
                'oid' => array ('required' => 1),
                'name' => array ('required' => 1),
                'description' => array ('default' => NULL),
                'idProduct' => array ('required' => 1),
                'rate' => array ('default' => 0),
                'quantity' => array ('default' => 0),
                'amountProduct' => array ('default' => 0),
                'amountTax' => array ('default' => 0),
                'amountDiscount' => array ('default' => 0),
                'amountShipping' => array ('default' => 0),
                'amountFinal' => array ('default' => 0),
                'dtCreated' => array (),
                'dtDeleted' => array ('default' => NULL),
                'sdata' => array ('default' => array ()),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
        
    }
    
}