<?php

namespace Module\System\Classes;

class EntitytypeHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array ()) {
        
        $options = array (
            'etid' => 'system--entitytype',
            'singularName' => 'entity type',
            'pluralName' => 'entity types',
            'tableName' => '%__system_entitytype',
            'keys' => array (
                'id' => 'etid',
            ),
            'properties' => array (
                'etid' => array (),
                'module' => array (),
                'name' => array (),
                'viewModes' => array (
                    'sdata' => 1,
                    'default' => array (
                        'default' => array (
                            'name' => 'Default',
                            'isLocked' => TRUE,
                        ),
                        'preview' => array (
                            'name' => 'Preview',
                            'isLocked' => TRUE,
                        ),
                    )
                ),
                'isAttributable' => array ()
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['etid']) )
            throw new \InvalidArgumentException('Missing required property "etid"');
        
        return parent::create($data);
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        
        if ( !$entity->module ):
            $etid_parts = explode('--', $entity->etid);
            $entity->module = $etid_parts[0];
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
}