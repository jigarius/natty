<?php

namespace Module\Commerce\Classes;

class CartitemHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--cartitem',
            'tableName' => '%__commerce_cartitem',
            'modelName' => array ('cart item', 'cart items'),
            'keys' => array (
                'id' => 'ciid',
            ),
            'properties' => array (
                'ciid' => array (),
                'idProduct' => array ('required' => 1),
                'idSession' => array ('default' => NULL),
                'idCreator' => array ('required' => 1),
                'idCustomer' => array ('required' => 1),
                'idCurrency' => array ('required' => 1),
                'xRate' => array (),
                'name' => array (),
                'description' => array ('default' => NULL),
                'rate' => array ('default' => 0),
                'quantity' => array ('default' => 0),
                'unitWeight' => array ('default' => 0),
                'totalWeight' => array ('default' => 0),
                'amountProduct' => array ('default' => 0),
                'amountTax' => array ('default' => 0),
                'amountDiscount' => array ('default' => 0),
                'amountShipping' => array ('default' => 0),
                'amountFinal' => array ('default' => 0),
                'dtCreated' => array (),
                'dtDeleted' => array ('default' => NULL),
                'sdata' => array ('default' => NULL),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        
        if ( !isset ($data['idCreator']) ):
            $user = \Natty::getUser();
            $data['idCreator'] = $user->uid;
            $data['idSession'] = session_id();
        endif;
        
        if (!isset ($data['idCustomer']))
            $data['idCustomer'] = $data['idCreator'];
        
        if ( !isset ($data['idCurrency']) )
            $data['idCurrency'] = \Natty::getCurrencyId();
        
        return parent::create($data);
        
    }
    
    public function validate($entity, array $options = array()) {
        
        if ( !$entity->idCreator && !$entity->idSession )
            throw new \Natty\ORM\EntityException('One of idSession or idCreator must be specified.');
        
        parent::validate($entity, $options);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
            
        // Load and apply product data
        $product = \Natty::getEntity('catalog--product', $entity->idProduct);
        if ( !$product )
            throw new \Natty\ORM\EntityException('Associated product could not be loaded.');

        // Load and apply currency data
        $currency = \Natty::getEntity('system--currency', $entity->idCurrency);
        if ( !$currency )
            throw new \Natty\ORM\EntityException('Associated currency could not be loaded.');
        $entity->xRate = $currency->xRate;

        $entity->name = $product->name;
        $entity->unitWeight = $product->weight;
        $entity->totalWeight = $product->weight * $entity->quantity;
        $entity->rate = $product->salePrice;
        $entity->amountProduct = $product->salePrice * $entity->quantity;
        $entity->amountShipping = $product->shippingCharge;

        $entity->amountFinal = $entity->amountProduct
                - $entity->amountDiscount
                + $entity->amountShipping
                + $entity->amountTax;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function buildFrontendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->uid === $entity->idCreator ):
            
            $output['delete'] = '<a href="' . \Natty::url('cart/action', array (
                'do' => 'delete',
                'with' => $entity->ciid,
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildFrontendLinks($entity, $options);
        
    }
    
}