<?php

namespace Module\Contact\Classes;

class CategoryHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'contact--category',
            'name' => array ('category', 'categories'),
            'tableName' => '%__contact_category',
            'keys' => array (
                'id' => 'cid',
            ),
            'properties' => array (
                'cid' => array (),
                'name' => array ('isTranslatable' => 1),
                'recipients' => array (),
                'ooa' => array (),
                'status' => array ('isTranslatable' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $auth_user = \Natty::getUser();
        
        $output = array ();
        if ( $auth_user->can('contact--administer') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/contact/category/' . $entity->cid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/contact/categories/action', array (
                'do' => 'delete',
                'with' => $entity->cid,
            )) . '">Delete</a>';
        endif;
        return $output + parent::buildBackendLinks($entity);
        
    }
    
}