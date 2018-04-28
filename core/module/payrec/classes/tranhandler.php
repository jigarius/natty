<?php

namespace Module\Payrec\Classes;

class TranHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'payrec--tran',
            'tableName' => '%__payrec_tran',
            'modelName' => array ('transaction', 'transactions'),
            'keys' => array (
                'id' => 'tid',
            ),
            'properties' => array (
                'tid' => array (),
                'tcode' => array (
                    'default' => NULL,
                ),
                'mid' => array (
                    'required' => 1,
                ),
                'contextType' => array (
                    'required' => 1,
                ),
                'contextId' => array (),
                'contextLabel' => array (),
                'contextUrl' => array ('default' => NULL),
                'name' => array (
                    'required' => 1,
                ),
                'variables' => array (
                    'default' => array (),
                    'serialized' => 1,
                ),
                'idCurrency' => array (
                    'required' => 1,
                ),
                'xRate' => array (
                    'required' => 1,
                ),
                'amount' => array (
                    'default' => 0,
                ),
                'idCreator' => array (
                    'default' => 0,
                ),
                'creatorName' => array (
                    'default' => NULL,
                ),
                'creatorEmail' => array (
                    'default' => NULL,
                ),
                'creatorMobile' => array (
                    'default' => NULL,
                ),
                'idVerifier' => array (
                    'default' => 0,
                ),
                'dtCreated' => array (
                    'default' => NULL,
                ),
                'dtVerified' => array (
                    'default' => NULL,
                ),
                'isStatusNotified' => array (
                    'default' => 0,
                ),
                'status' => array (
                    'default' => 0,
                ),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function allowAction($entity, $action, $user = NULL) {
        
        if (!$user)
            $user = \Natty::getUser ();
        
        $output = FALSE;
        
        switch ($action):
            case 'view':
                if ($entity->idCreator == $user->uid && $user->can('payrec--view own tran entities')):
                    $output = TRUE;
                    break;
                endif;
                $output = $user->can('payrec--view any tran entities');
                break;
            default:
                $output = $user->can('payrec--verify tran entities');
                break;
        endswitch;
        
        return $output;
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        // Add creator information
        if ($entity->idCreator):
            
            $creator = \Natty::getEntity('people--user', $entity->idCreator);
            
            if (!$entity->creatorName)
                $entity->creatorName = $creator->name;
            
            if (!$entity->creatorEmail)
                $entity->creatorEmail = $creator->email;
            
            if (!$entity->creatorMobile)
                $entity->creatorMobile = $creator->mobile;
            
        endif;
        
        // Handle success
        if (!$entity->status > 0):
            
            if (!$entity->dtVerified)
                $entity->dtVerified = date('Y-m-d h:i:s');
            
        endif;
        
        // Handle failure
        if ($entity->status < 0):
            
            if (!$entity->dtVerified)
                $entity->dtVerified = date('Y-m-d h:i:s');
            
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function onSave(&$entity, array $options = array()) {
        
        if (0 !== (int) $entity->status && !$entity->isStatusNotified):
            
            // Trigger an event
            \Natty::trigger('payrec--tran complete', $entity);
            \Natty::trigger('payrec--' . $entity->contextType . ' tran complete', $entity);
            
            // Mark event as triggered
            $entity->isStatusNotified = 1;
            \Natty::getDbo()->update($this->tableName, array (
                'tid' => $entity->tid,
                'isStatusNotified' => 1,
            ), array (
                'keys' => array ('tid'),
            ));
            
        endif;
        
        parent::onSave($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $output = array ();
        $auth_user = \Natty::getUser();
        
//        if ($auth_user->can('payment--manage method entities'))
//            $output['configure'] = '<a href="' . \Natty::url('backend/payment/methods/' . $entity->mid) . '">Configure</a>';
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}