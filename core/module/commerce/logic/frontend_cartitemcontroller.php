<?php

namespace Module\Commerce\Logic;

use \Module\Commerce\Classes\CheckoutHelper;

class Frontend_CartItemController {
    
    public static function pageManage() {
        
        // Load dependencies
        $cartitem_handler = \Natty::getHandler('commerce--cartitem');
        $cart_data = CheckoutHelper::readUserCartData();
        $response = \Natty::getResponse();
        $shipment_destination = CheckoutHelper::getShipmentDestination();
        
        // Read checkout data
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        $cid = \Natty::getCurrencyId();
        $currency = \Natty::getEntity('system--currency', $cid);
        
        // Carrier data
        $carrier_coll = CheckoutHelper::readCarrierOptions($shipment_destination);
        
        // Checkout request
        if ( isset ($_POST['checkout']) ):
            
            $response = \Natty::getResponse();
            
            if ( 0 === sizeof($cart_data['items']) ):
                \Natty\Console::error('Please add some items to your cart before you checkout.');
                $response->refresh();
            endif;
            
            $location = \Natty::url('checkout/account-setup');
            $response->redirect($location);
            
        endif;
        
        // Update cart
        if ( isset ($_POST['update']) ):
            
            // Update quantities
            foreach ($cart_data['items'] as $cartitem):
                
                if (!isset ($_POST['items'][$cartitem->ciid]))
                    continue;
                
                $cartitem_post = $_POST['items'][$cartitem->ciid];
                if (!is_numeric($cartitem_post['quantity']) || !$cartitem_post['quantity'] > 0)
                    continue;
                
                $cartitem->quantity = $cartitem_post['quantity'];
                $cartitem->save();
            
            endforeach;
            
            // Update carrier selection
            if (isset ($_POST['idCarrier']) && array_key_exists($_POST['idCarrier'], $carrier_coll)):
                $checkout_data['idCarrier'] = $_POST['idCarrier'];
            endif;
        
            $response->refresh();
            
        endif;
        
        // Additional form elements
        $form = array ();
        $form['idCarrier'] = array (
            '_render' => 'form_item',
            '_label' => 'Shipping method',
            '_description' => 'Click on update cart to preview your changes.',
            '_widget' => 'options',
            '_options' => array (),
            '_default' => $checkout_data['idCarrier'],
            'name' => 'idCarrier',
            'class' => array ('options-block'),
        );
        foreach ($carrier_coll as $carrier):
            $form['idCarrier']['_options'][$carrier->cid] = array (
                '_label' => $carrier->name,
                '_description' => $carrier->description,
            );
        endforeach;
        
        // Prepare output
        $output = array (
            '_render' => 'template',
            '_template' => 'module/commerce/tmpl/cartitem-list.tmpl',
        );
        $output['_data']['auth_user'] = \Natty::getUser();
        $output['_data']['cart_data'] = $cart_data;
        $output['_data']['checkout_data'] = $checkout_data;
        $output['_data']['shipment_destination'] = $shipment_destination;
        $output['_data']['currency'] = $currency;
        $output['_data']['carrier_options'] = $carrier_coll;
        $output['_data']['form'] = $form;
        
        $response->addScript(array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'commerce') . '/reso/checkouthelper.js'
        ));
        
        return $output;
        
    }
    
    public static function pageAction() {
        
        $action = \Natty::getRequest()->getString('do');
        $method = 'action' . natty_strtocamel($action, 1);
        if ( !method_exists(__CLASS__, $method) )
            \Natty::error(400);
        
        self::$method();
        
    }
    
    public static function actionDelete() {
        
        $request = \Natty::getRequest();
        $auth_user = \Natty::getUser();
        
        $cartitem = $request->getEntity('with', 'commerce--cartitem');
        if ( !$cartitem )
            \Natty::error(400);
        
        if ( !$cartitem->idUser != $auth_user->uid )
            \Natty::error(403);
        
        $cartitem->delete();
        $message = natty_replace(array (
            'name' => $cartitem->name,
        ), 'The item [@name] has been removed from your cart.');
        \Natty\Console::success($message);
        
        $location = \Natty::url('cart/items');
        \Natty::getResponse()->bounce($location);
        
    }
    
    public static function pageShipmentDestination() {
        
        $auth_user = \Natty::getUser();
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-shipment-destination-form'
        ));
        if ( $auth_user->uid > 0 ) {
            
            $uaddress_handler = \Natty::getHandler('location--useraddress');
            $uaddress_opts = $uaddress_handler->readOptions(array (
                'idUser' => $auth_user->uid,
                'status' => 1,
            ));
            
            $form->items['idShippingAddress'] = array (
                '_label' => 'Shipping address',
                '_description' => 'Address not on the list? <a href="' . \Natty::url('backend/people/users/' . $auth_user->uid . '/addresses/create', array (
                    'bounce' => TRUE,
                )) . '">Click here</a> to create a new address.',
                '_widget' => 'dropdown',
                '_options' => $uaddress_opts,
                '_default' => $checkout_data['idShippingAddress'],
                'placeholder' => ' ',
                'required' => 1,
            );
            
        }
        else {
            
            // Country options
            $country_opts = \Natty::getHandler('location--country')->readOptions(array (
                'key' => array ('status' => 1),
            ));
            $form->items['cid'] = array (
                '_label' => 'Country',
                '_widget' => 'dropdown',
                '_options' => $country_opts,
                '_default' => $checkout_data['destination']['cid'],
                'id' => 'fw-cid',
                'required' => 1,
            );
            if (1 === sizeof($country_opts))
                $form->items['cid']['readonly'] = 1;
            
            // State options
            $form->items['sid'] = array (
                '_label' => 'State',
                '_widget' => 'dropdown',
                '_default' => $checkout_data['destination']['sid'],
                '_possibleValues' => '*',
                'id' => 'fw-sid',
                'placeholder' => 'Any',
                'data-ui-init' => array ('state-picker'),
                'data-state-picker-country-picker' => '#fw-cid',
            );
            
            // Region options
            $form->items['rid'] = array (
                '_label' => 'Region',
                '_widget' => 'dropdown',
                '_default' => $checkout_data['destination']['rid'],
                '_possibleValues' => '*',
                'id' => 'fw-rid',
                'placeholder' => 'Any',
                'data-ui-init' => array ('region-picker'),
                'data-region-picker-state-picker' => '#fw-sid',
            );
            
        }
        
        $form->scripts[] = array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'location') . '/reso/state-picker.js',
        );
        $form->scripts[] = array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'location') . '/reso/region-picker.js',
        );
        
        $form->actions['save'] = array (
            '_label' => 'Update',
        );
        $form->actions['back'] = array (
            '_label' => 'Discard changes',
            'type' => 'anchor',
            'href' => \Natty::url('cart/items'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted('save') ):
            
            $form_data = $form->getValues();
            
            // Validate values
            if ( $form_data['cid'] ):
                $country = \Natty::getEntity('location--country', $form_data['cid'], array (
                    'key' => array (
                        'status' => 1
                    ),
                ));
                if (!$country)
                    $form->items['cid']['_errors'][] = 'Invalid value.';
            endif;
            
            if ( $form_data['sid'] ) {
                $state = \Natty::getEntity('location--state', $form_data['sid'], array (
                    'key' => array (
                        'cid' => $form_data['cid'],
                        'status' => 1
                    ),
                ));
                if (!$state)
                    $form->items['sid']['_errors'][] = 'Invalid value.';
            }
            else {
                $form_data['sid'] = FALSE;
            }
            
            if ( $form_data['rid'] ) {
                $region = \Natty::getEntity('location--region', $form_data['rid'], array (
                    'key' => array (
                        'cid' => $form_data['cid'],
                        'sid' => $form_data['sid'],
                        'status' => 1
                    ),
                ));
                if (!$region)
                    $form->items['rid']['_errors'][] = 'Invalid value.';
            }
            else {
                $form_data['rid'] = FALSE;
            }
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isSubmitted('save') && $form->isValid() ):
            
            if ( $auth_user->uid > 0 ) {
                $checkout_data['idShippingAddress'] = $form_data['idShippingAddress'];
            }
            else {
                $checkout_data['destination'] = array_merge($checkout_data['destination'], $form_data);
            }
            
            \Natty\Console::success();
            $form->redirect = \Natty::url('cart/items');
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}