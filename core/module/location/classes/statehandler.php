<?php

namespace Module\Location\Classes;

class StateHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'location--state',
            'tableName' => '%__location_state',
            'modelName' => array ('state', 'states'),
            'keys' => array (
                'id' => 'sid',
                'label' => 'name',
            ),
            'properties' => array (
                'sid' => array (),
                'scode' => array (),
                'cid' => array ('required' => 1),
                'nativeName' => array (),
                'name' => array ('isTranslatable' => 1, 'required' => 1),
                'status' => array ('default' => 1),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('system--administer') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states/' . $entity->sid) . '">Edit</a>';
            $output['regions'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states/' . $entity->sid . '/regions') . '">Manage regions</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states/' . $entity->sid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}