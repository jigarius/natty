<?php

namespace Module\System\Classes;

class IncidentHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'system--incident',
            'tableName' => '%__system_incident',
            'singularName' => 'incident',
            'pluralName' => 'incidents',
            'keys' => array (
                'id' => 'iid',
                'label' => 'type'
            ),
            'properties' => array (
                'iid' => array (),
                'key' => array ('default' => NULL),
                'idCreator' => array ('default' => 0),
                'remoteIp' => array (),
                'type' => array ('system--general'),
                'key' => array ('default' => NULL),
                'description' => array (),
                'variables' => array ('serialized' => TRUE),
                'originUrl' => array (),
                'refererUrl' => array ('default' => NULL),
                'tsCreated' => array (),
                'tsExpired' => array ('default' => NULL),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        // Add remote IP
        if ( !isset ($data['remoteIp']) )
            $data['remoteIp'] = $_SERVER['REMOTE_ADDR'];
        
        // Add creator ID
        if ( !isset ($data['idCreator']) ):
            $user = \Natty::getUser();
            $data['idCreator'] = $user->uid;
        endif;
        
        // Add origin URL
        if ( !isset ($data['originUrl']) )
            $data['originUrl'] = $_SERVER['REQUEST_URI'];
        
        // Add referer URL
        if ( !isset ($data['refererUrl']) )
            $data['refererUrl'] = $_SERVER['HTTP_REFERER'];
        
        // Add creation time
        if ( !isset ($data['tsCreated']) )
            $data['tsCreated'] = time();
        
        return parent::create($data);
        
    }
    
    public function onBeforeSave(&$entity, array $options = array ()) {
        
        // Add ID, if new
        if ( !$entity->iid )
            $entity->iid = microtime(TRUE) . ':' . $_SERVER['REMOTE_ADDR'] . ':' . rand(0,1000);
        
        parent::onBeforeSave($entity, $options);
        
    }    
    
}