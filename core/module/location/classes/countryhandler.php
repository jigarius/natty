<?php

namespace Module\Location\Classes;

class CountryHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'location--country',
            'tableName' => '%__location_country',
            'modelName' => array ('country', 'countries'),
            'keys' => array (
                'id' => 'cid',
                'label' => 'name',
            ),
            'properties' => array (
                'cid' => array (),
                'isoNumCode' => array (),
                'iso2Code' => array (),
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
            
            $output['edit'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid) . '">Edit</a>';
            $output['states'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states') . '">Manage states</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}