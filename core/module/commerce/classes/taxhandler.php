<?php

namespace Module\Commerce\Classes;

class TaxHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--tax',
            'tableName' => '%__commerce_tax',
            'modelName' => array ('tax', 'taxes'),
            'keys' => array (
                'id' => 'tid',
            ),
            'properties' => array (
                'tid' => array (),
                'name' => array ('isTranslatable' => 1),
                'rate' => array ('default' => 0),
                'dtCreated' => array (),
                'dtDeleted' => array ('default' => NULL),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        if ( $entity->status < 0 && !$entity->dtDeleted )
            $entity->dtDeleted = date('Y-m-d H:i:s');
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('commerce--manage tax entities') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/commerce/taxes/' . $entity->tid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/commerce/taxes/action', array (
                'do' => 'delete',
                'with' => $entity->tid,
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}