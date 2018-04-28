<?php

namespace Module\Location\Classes;

class UseraddressHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'location--useraddress',
            'tableName' => '%__location_useraddress',
            'modelName' => array ('address', 'addresses'),
            'keys' => array (
                'id' => 'aid',
            ),
            'properties' => array (
                'aid' => array (),
                'idUser' => array ('required' => 1),
                'name' => array ('required' => 1),
                'body' => array (),
                'city' => array ('default' => NULL),
                'landmark' => array ('default' => NULL),
                'postCode' => array ('default' => NULL),
                'cid' => array ('default' => NULL),
                'sid' => array ('default' => NULL),
                'rid' => array ('default' => NULL),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function getQuery(array $options = array()) {
        
        $query = parent::getQuery($options);
        
        $query
                ->addJoin('left', '%__location_country_i18n country_i18n', '{country_i18n}.{cid} = {useraddress}.{cid}')
                ->addJoin('left', '%__location_state_i18n state_i18n', '{state_i18n}.{sid} = {useraddress}.{sid}')
                ->addJoin('left', '%__location_region_i18n region_i18n', '{region_i18n}.{rid} = {useraddress}.{rid}')
                ->addColumn('country_i18n.name countryName')
                ->addColumn('state_i18n.name stateName')
                ->addColumn('region_i18n.name regionName');
        
        return $query;
        
    }
    
    public function allowAction($entity, $action, $user = NULL) {
        
        if ( !$user )
            $user = \Natty::getUser();
        
        if ( $user->can('location--manage any address entities') )
            return TRUE;
        if ( $entity->idUser == $user->uid && $user->can('location--manage own address entities') )
            return TRUE;
        
        return FALSE;
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $auth_user = \Natty::getUser();
        $output = array ();
        
        if ( $this->allowAction($entity, 'edit') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/people/users/' . $entity->idUser . '/addresses/' . $entity->aid) . '">Edit</a>';
        endif;
        
        if ( $this->allowAction($entity, 'delete') ):
            $output['delete'] = '<a href="' . \Natty::url('backend/people/users/' . $entity->idUser . '/addresses/' . $entity->aid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        $extras = parent::buildBackendLinks($entity, $options);
        return array_merge($output, $extras);
        
    }
    
}