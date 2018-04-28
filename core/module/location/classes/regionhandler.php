<?php

namespace Module\Location\Classes;

class RegionHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'location--region',
            'tableName' => '%__location_region',
            'modelName' => array ('region', 'regions'),
            'keys' => array (
                'id' => 'rid',
                'label' => 'name',
            ),
            'properties' => array (
                'rid' => array (),
                'cid' => array ('required' => 1),
                'sid' => array ('required' => 1),
                'name' => array ('isTranslatable' => 1, 'required' => 1),
                'hasPostCodeData' => array ('default' => 0),
                'status' => array ('default' => 1),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function getQuery(array $options = array()) {
        $query = parent::getQuery($options);
        $query
                ->addColumn('state_i18n.name stateName')
                ->addColumn('country_i18n.name countryName')
                ->addJoin('left', '%__location_state_i18n state_i18n', '{state_i18n}.{sid} = {region}.{sid} AND {state_i18n}.{ail} = :ail')
                ->addJoin('left', '%__location_country_i18n country_i18n', '{country_i18n}.{cid} = {region}.{cid} AND {country_i18n}.{ail} = :ail');
        return $query;
    }
    
    public function onBeforeSave(&$entity, array $options = array()) {
        
        $state = \Natty::getEntity('location--state', $entity->sid);
        if ( !$state )
            throw new \Natty\ORM\EntityException('Required property "sid" has invalid value.');
        $entity->cid = $state->cid;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('system--administer') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states/' . $entity->sid . '/regions/' . $entity->rid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/location/countries/' . $entity->cid . '/states/' . $entity->sid . '/regions/' . $entity->rid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}