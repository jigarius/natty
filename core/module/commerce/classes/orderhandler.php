<?php

namespace Module\Commerce\Classes;

class OrderHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--order',
            'tableName' => '%__commerce_order',
            'modelName' => array ('order', 'orders'),
            'keys' => array (
                'id' => 'oid',
                'code' => 'ocode',
            ),
            'properties' => array (
                'oid' => array (
                ),
                'ocode' => array (
                    'default' => NULL,
                ),
                'idCreator' => array (
                    'required' => 1
                ),
                'idCustomer' => array (
                    'required' => 1
                ),
                'billingName' => array (
                    'required' => 1,
                ),
                'idBillingAddress' => array (
                    'required' => 1,
                ),
                'shippingName' => array (
                    'required' => 1,
                ),
                'idShippingAddress' => array (
                    'required' => 1,
                ),
                'idCurrency' => array (
                    'required' => 1,
                ),
                'xRate' => array (
                    'required' => 1,
                ),
                'amountProduct' => array (
                    'default' => 0,
                ),
                'amountTax' => array (
                    'default' => 0,
                ),
                'amountDiscount' => array (
                    'default' => 0,
                ),
                'amountShipping' => array (
                    'default' => 0,
                ),
                'amountFinal' => array (
                    'default' => 0,
                ),
                'amountPaid' => array (
                    'default' => 0,
                ),
                'amountRefunded' => array (
                    'default' => 0,
                ),
                'idCarrier' => array (
                    'required' => 1,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'default' => NULL,
                ),
                'sdata' => array (
                    'default' => array (),
                ),
                'idActualStatus' => array (),
                'idVisibleStatus' => array (),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if (!$data['idCreator'])
            $data['idCreator'] = \Natty::getUser();
        if (!$data['idCustomer'])
            $data['idCustomer'] = $data['idCreator'];
        
        return parent::create($data);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        // Read unchanged order
        $entity->readUnchanged();
        
        // Assign default status
        if (!$entity->idVisibleStatus)
            $entity->idVisibleStatus = TaskstatusHandler::ID_AWAITING_PAYMENT;
        
        // Assign actual status
        if (!$entity->idActualStatus):
            $tstatus = \Natty::getEntity('commerce--taskstatus', $entity->idVisibleStatus);
            $entity->idActualStatus = $entity->parentId > 0
                    ? $tstatus->parentId : $entity->idVisibleStatus;
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        
        /**
         * @todo If the order is marked as anything more than "awaiting payment"
         * and the old order status was "awaiting payment", assume that payment
         * has been received.
         */
        
        // Generate order code
        if ( !$entity->ocode ):
            
            $year = date('Y');
            $onum = \Module\System\Classes\SerialHelper::generate('commerce.order', $year);
            
            $entity->ocode = $year . '-' . str_pad($onum, 4, '0', STR_PAD_LEFT);
            
            // Update the database. Cannot use entity->save() here.
            $dbo = $this->getDbo();
            $dbo->update($this->tableName, array (
                'ocode' => $entity->ocode,
            ), array (
                'key' => array (
                    'oid' => $entity->oid,
                ),
            ));
            
        endif;
        
        parent::onSave($entity, $options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array()) {
        
        // Delete order items
        $orderitem_handler = \Natty::getHandler('commerce-orderitem');
        $orderitem_coll = $orderitem_handler->read(array (
            'key' => array ('oid' => $entity->oid),
        ));
        foreach ($orderitem_coll as $orderitem):
            $orderitem->delete();
        endforeach;
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ($this->allowAction($entity, 'view', $user)):
            $output['view'] = '<a href="' . \Natty::url('backend/commerce/orders/' . $entity->oid) . '">View</a>';
        endif;
        
        return $output + parent::buildFrontendLinks($entity, $options);
        
    }
    
    public function allowAction($entity, $action, $user = NULL) {
        
        if (!$user)
            $user = \Natty::getUser();
        
        switch ($action):
            case 'view':
                if ($user->uid == $entity->idCreator)
                    return $user->can('commerce--manage own order entities');
                return $user->can('commerce--manage any order entities');
        endswitch;
        
        parent::allowAction($entity, $action, $user);
        
    }
    
    public function getEntityLabel($entity) {
        return 'Order ' . $entity->ocode;
    }
    
}