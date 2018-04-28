<?php

namespace Module\System\Classes;

class RewriteHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'system--rewrite',
            'modelName' => array ('rewrite', 'rewrites'),
            'tableName' => '%__system_rewrite',
            'keys' => array (
                'id' => 'rid',
                'label' => 'customUrl',
            ),
            'properties' => array (
                'rid' => array (),
                'systemPath' => array (),
                'customPath' => array (),
                'ail' => array (),
                'dtCreated' => array (),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        if ( $entity->isNew ):
            $entity->dtCreated = date('Y-m-d H:i:s');
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('system--manage rewrite') ):
            $output['delete'] = '<a href="' . \Natty::url('backend/system/rewrites/action', array (
                'do' => 'delete', 'with' => $entity->rid,
            )) . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        return $output;
        
    }
    
}
