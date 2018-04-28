<?php

namespace Module\Commerce\Classes;

class TaxgroupHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--taxgroup',
            'tableName' => '%__commerce_taxgroup',
            'modelName' => array ('tax group', 'tax groups'),
            'keys' => array (
                'id' => 'tgid',
            ),
            'properties' => array (
                'tgid' => array (),
                'name' => array (),
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
            
            $output['edit'] = '<a href="' . \Natty::url('backend/commerce/tax-groups/' . $entity->tgid) . '">Edit</a>';
            $output['tax-rules'] = '<a href="' . \Natty::url('backend/commerce/tax-groups/' . $entity->tgid . '/tax-rules') . '">Manage rules</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/commerce/tax-groups/action', array (
                'do' => 'delete',
                'with' => $entity->tgid,
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}