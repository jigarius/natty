<?php

namespace Module\People\Classes;

class TokenHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'people--token',
            'tableName' => '%__people_token',
            'modelName' => array ('token', 'tokens'),
            'keys' => array (
                'id' => 'tid',
            ),
            'properties' => array (
                'tid' => array (),
                'uid' => array (),
                'purpose' => array (),
                'dtCreated' => array (),
                'dtExpired' => array ('default' => NULL),
                'sdata' => array ('serialized' => TRUE),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function validate($entity, array $options = array()) {
        
        if ( !$entity->getVar('purpose') )
            throw new \Natty\ORM\EntityException('Required property "purpose" cannot be empty.');
        
        if ( !$entity->getVar('uid') )
            throw new \Natty\ORM\EntityException('Required property "uid" cannot be empty.');
        
        parent::validate($entity, $options);
        
    }
    
    public function onBeforeSave(&$entity, array $options = array()) {
        
        if ( !$entity->tid )
            $entity->tid = natty_rand_string();
        
        if ( !$entity->dtExpired )
            $entity->dtExpired = NULL;
        
        return parent::onBeforeSave($entity, $options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['dtCreated']) || empty ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
        
    }
    
}