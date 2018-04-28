<?php

namespace Module\Commerce\Classes;

class TaxruleHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    const BEHAVIOR_REPLACE = 'r';
    const BEHAVIOR_COMBINE = 'c';
    const BEHAVIOR_SUCCEED = 's';
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--taxrule',
            'tableName' => '%__commerce_taxrule',
            'modelName' => array ('tax rule', 'tax rules'),
            'keys' => array (
                'id' => 'trid',
            ),
            'properties' => array (
                'trid' => array (),
                'tgid' => array (),
                'tid' => array (),
                'description' => array ('default' => NULL),
                'behavior' => array ('default' => NULL),
                'ooa' => array (),
                'idCountry' => array (),
                'idState' => array ('default' => NULL),
                'dtCreated' => array (),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('commerce--manage tax entities') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/commerce/tax-groups/' . $entity->tgid . '/tax-rules/' . $entity->trid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/commerce/tax-rules/action', array (
                'do' => 'delete',
                'with' => $entity->trid,
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}