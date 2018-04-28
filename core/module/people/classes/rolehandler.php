<?php

namespace Module\People\Classes;

class RoleHandler 
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    const ID_ADMINISTRATOR = 1;
    const ID_ANONYMOUS = 2;
    const ID_MEMBER = 3;
    
    public function __construct() {
        parent::__construct(array (
            'tableName' => '%__people_role',
            'etid' => 'people--role',
            'keys' => array (
                'id' => 'rid',
                'label' => 'name',
            ),
            'singularName' => 'role',
            'pluralName' => 'roles',
            'properties' => array (
                'rid' => array (),
                'name' => array (),
                'isLocked' => array ('default' => 0),
                'status' => array ('default' => 0)
            )
        ));
    }
    
    protected function onBeforeDelete(&$entity, array $options = array()) {
        
        // Delete permissions
        \Natty::getDbo()->delete('%__people_role_permission', array (
            'key' => array ('rid' => $entity->rid),
        ));
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('people--manage role entities') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/people/roles/' . $entity->rid . '/edit') . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/people/roles/action', array (
                'do' => 'delete', 'with' => $entity->rid
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        if ( $user->can('people--manage permissions') )
            $output['permissions'] = '<a href="' . \Natty::url('backend/people/roles/' . $entity->rid . '/permissions') . '">Permissions</a>';
        
        $override = parent::buildBackendLinks($entity, $options);
        return array_merge($output, $override);
        
    }
    
}