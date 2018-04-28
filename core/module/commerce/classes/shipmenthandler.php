<?php

namespace Module\Commerce\Classes;

class ShipmentHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--shipment',
            'modelName' => array ('shipment', 'shipments'),
            'keys' => array (
                'id' => 'sid',
                'label' => 'description',
            ),
            'properties' => array (
                'sid' => array (),
                'scode' => array (
                    'default' => NULL,
                ),
                'oid' => array (
                    'required' => 1,
                ),
                'cid' => array (
                    'required' => 1,
                ),
                'description' => array (
                    'required' => 1,
                ),
                'idCreator' => array (
                    'required' => 1
                ),
                'idVerifier' => array (
                    'default' => NULL,
                ),
                'dtCreated' => array (
                    'required' => 1
                ),
                'dtVerified' => array (
                    'default' => NULL
                ),
                'status' => array (
                    'default' => 0,
                ),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        if (!$entity->idCreator):
            $auth_user = \Natty::getUser();
            $entity->idCreator = $auth_user->uid;
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
}