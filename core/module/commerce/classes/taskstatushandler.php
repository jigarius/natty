<?php

namespace Module\Commerce\Classes;

class TaskstatusHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    const ID_CANCELED = 1;
    const ID_AWAITING_PAYMENT = 2;
    const ID_VERIFYING_PAYMENT = 3;
    const ID_PENDING = 4;
    const ID_PROCESSING = 5;
    const ID_PROCESSED = 6;
    const ID_DISPATCHED = 7;
    const ID_DELIVERED = 8;
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--taskstatus',
            'tableName' => '%__commerce_taskstatus',
            'modelName' => array ('status', 'statuses'),
            'keys' => array (
                'id' => 'tsid',
            ),
            'properties' => array (
                'tsid' => array (),
                'parentId' => array (),
                'name' => array ('isTranslatable' => 1),
                'description' => array ('isTranslatable' => 1),
                'colorCode' => array ('default' => NULL),
                'isLocked' => array ('default' => 0),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('commerce--manage taskstatus entities') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/commerce/task-statuses/' . $entity->tsid) . '">Edit</a>';
            if ( !$entity->isLocked ):
                $output['delete'] = '<a href="' . \Natty::url('backend/commerce/task-statuses/' . $entity->tsid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
            endif;
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    public function readOptions(array $options = array()) {
        
        if (!isset ($options['ordering']))
            $options['ordering'] = array ('tsid' => 'asc',);
        
        return parent::readOptions($options);
        
    }
    
}