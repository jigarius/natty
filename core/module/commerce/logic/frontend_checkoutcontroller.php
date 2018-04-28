<?php

namespace Module\Commerce\Logic;

use \Module\Commerce\Classes\CheckoutHelper;

class Frontend_CheckoutController {
    
    public static function pageAccountSetup() {
        
        $auth_user = \Natty::getUser();
        if ( !$auth_user->uid )
            return \Module\People\Logic\Frontend_UserController::pageSignIn();
        
        $location = \Natty::url('checkout/address-setup');
        \Natty::getResponse()->redirect($location);
        
    }
    
    public static function pageAddressSetup() {
        
        // Load dependencies
        $auth_user = \Natty::getUser();
        $uaddress_handler = \Natty::getHandler('location--useraddress');
        $cartitem_handler = \Natty::getHandler('commerce--cartitem');
        
        // Temp checkout data
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-checkout-address-setup',
        ));
        
        // Read items
        $list_data = $uaddress_handler->read(array (
            'key' => array (
                'idUser' => $auth_user->uid,
            ),
        ));
        
        // Did the user just create a shipping?
        if ( isset ($_REQUEST['did']) ):
            
            if ( 'create-shipping' === $_REQUEST['did'] ):
                $last_address = array_pop($list_data);
                $checkout_data['idShippingAddress'] = $last_address->aid;
            endif;
            if ( 'create-billing' === $_REQUEST['did'] ):
                $last_address = array_pop($list_data);
                $checkout_data['idBillingAddress'] = $last_address->aid;
            endif;
            
            $command = \Natty::getCommand();
            $location = \Natty::url($command);
            \Natty::getResponse()->redirect($location);
            
        endif;
        
        // List of shipping addresses
        $list_shipping = array (
            '_render' => 'list',
            '_items' => array (),
            'class' => array ('n-list', 'n-list-grid', 'address-list'),
        );
        $aid_shipping = isset ($_POST['idShippingAddress'])
                ? $_POST['idShippingAddress'] : $checkout_data['idShippingAddress'];
        
        // List of billing addresses
        $list_billing = array (
            '_render' => 'list',
            '_items' => array (),
            'class' => array ('n-list', 'n-list-grid', 'address-list'),
        );
        $aid_billing = isset ($_POST['idBillingAddress'])
                ? $_POST['idBillingAddress'] : $checkout_data['idBillingAddress'];
        
        // Build a list of address options
        foreach ($list_data as $address):
            
            $item_shipping = array ();
            $item_shipping[] = array (
                '_render' => 'form_item',
                '_widget' => 'input',
                '_description' => $address->name,
                'name' => 'idShippingAddress',
                'value' => $address->aid,
                'type' => 'radio',
                'checked' => $address->aid == $aid_shipping,
            );
            $item_shipping[] = $address->render();
            $item_shipping[] = '<p><a href="' . \Natty::url('backend/people/users/' . $auth_user->uid . '/addresses/' . $address->aid, array (
                'bounce' => TRUE,
            )) . '"><i class="n-icon n-icon-edit"></i> Edit</a></p>';
            $list_shipping['_items'][] = $item_shipping;
            
            $item_billing = array ();
            $item_billing[] = array (
                '_render' => 'form_item',
                '_widget' => 'input',
                '_description' => $address->name,
                'name' => 'idBillingAddress',
                'value' => $address->aid,
                'type' => 'radio',
                'checked' => $address->aid == $aid_billing,
            );
            $item_billing[] = $address->render(array (
                'heading' => 1,
            ));
            $item_billing[] = '<p><a href="' . \Natty::url('backend/people/users/' . $auth_user->uid . '/addresses/' . $address->aid, array (
                'bounce' => TRUE,
            )) . '"><i class="n-icon n-icon-edit"></i> Edit</a></p>';
            $list_billing['_items'][] = $item_billing;
            
        endforeach;
        
        // Create address link
        $item_create = '<div class="n-emptytext"><a href="' 
                . \Natty::url('backend/people/users/' . $auth_user->uid . '/addresses/create', array (
                    'bounce' => \Natty::url('checkout/address-setup', array (
                        'did' => 'create-shipping',
                    ))
                ))
                . '">Create</a></div>';
        $list_shipping['_items'][] = $item_create;
        
        $item_create = '<div class="n-emptytext"><a href="' 
                . \Natty::url('backend/people/users/' . $auth_user->uid . '/addresses/create', array (
                    'bounce' => \Natty::url('checkout/address-setup', array (
                        'did' => 'create-billing',
                    ))
                ))
                . '">Create</a></div>';
        $list_billing['_items'][] = $item_create;
        
        $form->items['shipping'] = array (
            '_label' => 'Delivery Info',
            '_widget' => 'container',
            '_data' => array (),
        );
        $form->items['shipping']['_data']['shippingName'] = array (
            '_label' => 'Delivery name',
            '_description' => 'The person in whose name the package should be addressed.',
            '_widget' => 'input',
            '_default' => $checkout_data['shippingName'],
            'maxlength' => 255,
            'required' => 1,
        );
        $form->items['shipping']['_data']['idShippingAddress'] = array (
            '_label' => 'Delivery address',
            '_widget' => 'markup',
            '_data' => $list_shipping,
            'required' => 1,
        );
        
        $form->items['billing'] = array (
            '_label' => 'Billing Info',
            '_widget' => 'container',
            '_data' => array (),
        );
        
        // Billing equals shipping
        $form->items['billing']['_data']['bes'] = array (
            '_description' => 'My billing information is the same as my shipping information.',
            '_widget' => 'input',
            '_default' => $checkout_data['bes'],
            'type' => 'checkbox',
            'id' => 'fi-bes',
        );
        
        $form->items['billing']['_data']['billingName'] = array (
            '_label' => 'Shipping name',
            '_description' => 'The person in whose name the package should be addressed.',
            '_widget' => 'input',
            '_default' => $checkout_data['billingName'],
            'maxlength' => 255,
        );
        $form->items['billing']['_data']['idBillingAddress'] = array (
            '_label' => 'Billing address',
            '_widget' => 'markup',
            '_data' => $list_billing,
        );
        
        $form->actions['next'] = array (
            '_label' => 'Next step'
        );
        
        // Add javascript
        $form->scripts[] = <<<'EOS'
jQuery("#fi-bes").change(function(){
    var checked = this.checked;
    var $fi = jQuery(".form-item-billingName, .form-item-idBillingAddress");
    if (checked)
        $fi.hide();
    else
        $fi.show();
}).change();
EOS;
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
            
            if ( !$form_data['idShippingAddress'] ):
                $form->items['idShippingAddress']['_errors'][] = 'Please specify an address.';
                $form->isValid(FALSE);
            endif;
            
            if ( $form_data['bes'] ):
                $form_data['billingName'] = $form_data['shippingName'];
                $form_data['idBillingAddress'] = $form_data['idShippingAddress'];
            endif;
            
            if ( !$form_data['idBillingAddress'] ):
                $form->items['idBillingAddress']['_errors'][] = 'Please specify an address.';
                $form->isValid(FALSE);
            endif;
            
            $form->onValidate();
        
        endif;
        
        // Process form
        if ( $form->isSubmitted('next') && $form->isValid() ):
            
            // Clear carrier ID on address change
            if ($form_data['idShippingAddress'] != $checkout_data['idShippingAddress']):
                $checkout_data['idCarrier'] = NULL;
            endif;
            
            // Save address data
            $checkout_data = array_merge($checkout_data, $form_data);
            
            $location = \Natty::url('checkout/confirm-order');
            \Natty::getResponse()->redirect($location);
            
        endif;
        
        // Prepare output
        $output = array ();
        $output['form'] = $form->getRarray();
        
        return $output;
        
        
    }
    
    public static function pageConfirmOrder() {
        
        // See if the user has finished other steps
        self::_jumpToStep();
        
        // Load dependencies
        $cartitem_handler = \Natty::getHandler('commerce--cartitem');
        $auth_user = \Natty::getUser();
        $cart_data = CheckoutHelper::readUserCartData();
        
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        // Load currency
        $cid = \Natty::getCurrencyId();
        $currency = \Natty::getEntity('system--currency', $cid);
        
        // Load billing and shipping address
        $shipping_address = \Natty::getEntity('location--useraddress', $checkout_data['idShippingAddress']);
        $billing_address = \Natty::getEntity('location--useraddress', $checkout_data['idBillingAddress']);
        $address_setup_url = \Natty::url('checkout/address-setup');
        
        // Build checkout form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-checkout-confirm-order',
        ));
        
        // Read shipment carrier
        $carrier = \Natty::getEntity('commerce--carrier', $checkout_data['idCarrier']);
        $form->items['idCarrier'] = array (
            '_widget' => 'options',
            '_label' => 'Delivery method',
            '_options' => array (
                $carrier->cid => array (
                    '_data' => $carrier->name,
                    '_description' => $carrier->description,
                ),
            ),
            '_default' => $carrier->cid,
            '_ignore' => 1,
            'disabled' => 'disabled',
            'required' => TRUE,
        );
        
        // If payment module is enabled, show available payment methods
        if ($mod_payrec = \Natty::getPackage('module', 'payrec')):
            
            $pmethod_handler = \Natty::getHandler('payrec--method');
            $pmethod_coll = $pmethod_handler->readByKeys(array (
                'type' => 'receipt',
                'status' => 1,
            ));
            $form->items['idPaymentMethod'] = array (
                '_widget' => 'options',
                '_label' => 'Payment method',
                '_options' => array (),
                'required' => TRUE,
            );
            foreach ($pmethod_coll as $pmethod):
                $form->items['idPaymentMethod']['_options'][$pmethod->mid] = array (
                    '_data' => $pmethod->name,
                    '_description' => $pmethod->description,
                );
            endforeach;
            
            if (1 === sizeof($pmethod_coll)):
                $form->items['idPaymentMethod']['_default'] = $pmethod->mid;
            endif;
            
        endif;
        
        $form->actions['confirm-order'] = array (
            '_label' => 'Confirm order',
            'class' => array ('pull-right'),
        );
        $form->actions['back'] = array (
            '_label' => 'Checkout later',
            'type' => 'anchor',
            'href' => \Natty::url('catalog'),
        );
        
        $form->onPrepare();
        
        // Request to checkout?
        if ($form->isSubmitted('confirm-order')):
            
            $form_data = $form->getValues();
            $form->onValidate();
        
            if ( $form->isValid() ):
                
                try {
                    
                    $order = self::_convertCartToOrder();

                    // Redirect to order view page
                    \Natty\Console::success('Your order has been created successfully.');
                    
                    // If payment module is enabled, initiate payment
                    if ($mod_payrec):
                        
                        $pmethod = $pmethod_coll[$form_data['idPaymentMethod']];
                        $pmethod_handler->doTransaction($pmethod, array (
                            'mid' => $pmethod->mid,
                            'contextType' => 'commerce--order',
                            'contextId' => $order->oid,
                            'contextLabel' => 'Order ' . $order->ocode,
                            'contextUrl' => 'backend/commerce/orders/' . $order->oid,
                            'idCreator' => $order->idCreator,
                            'name' => 'Payment for order [@ocode]',
                            'variables' => array (
                                'ocode' => $order->ocode,
                            ),
                            'idCurrency' => $order->idCurrency,
                            'xRate' => $order->xRate,
                            'amount' => $order->amountFinal,
                        ));
                        
                        // For cash payment, mark order as pending
                        if ('cash' === $pmethod->mid)
                            $order->isVisibleStatus = \Module\Commerce\Classes\TaskstatusHandler::ID_PENDING;
                        
                    endif;
                    
                    // Redirect to order page
                    $location = 'backend/commerce/orders/' . $order->oid;
                    \Natty::getResponse()->redirect($location);

                }
                catch (\Exception $ex) {
                    
                    \Natty\Console::error($ex->getMessage());
                    \Natty::getResponse()->refresh();
                    
                }
                
            endif;
        
        endif;
        
        // Prepare output
        $output = array (
            '_render' => 'template',
            '_template' => 'module/commerce/tmpl/checkout-confirm-order.tmpl',
            '_data' => array (
                'currency' => $currency,
                'shippingName' => $checkout_data['shippingName'],
                'shippingAddress' => $shipping_address,
                'billingName' => $checkout_data['billingName'],
                'billingAddress' => $billing_address,
                'addressSetupUrl' => $address_setup_url,
                'form' => $form->getRarray(),
                'cart' => $cart_data,
            ),
        );
        
        return $output;
        
    }
    
    public static function _convertCartToOrder() {
        
        // Load dependencies
        $dbo = \Natty::getDbo();
        $cartitem_handler = \Natty::getHandler('commerce--cartitem');
        $order_handler = \Natty::getHandler('commerce--order');
        $orderitem_handler = \Natty::getHandler('commerce--orderitem');
        $currency_id = \Natty::readSetting('system--currency');
        $currency = \Natty::getEntity('system--currency', $currency_id);
        $cart_data = CheckoutHelper::readUserCartData();
        $auth_user = \Natty::getUser();
        
        // Validate checkout data
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        try {
        
            $dbo->beginTransaction();
            
            // Create order
            $order = $order_handler->createAndSave(array (
                'idCreator' => $auth_user->uid,
                'idCustomer' => $auth_user->uid,
                'idCurrency' => $currency->cid,
                'idCarrier' => $checkout_data['idCarrier'],
                'xRate' => $currency->xRate,
                'billingName' => $checkout_data['billingName'],
                'idBillingAddress' => $checkout_data['idBillingAddress'],
                'shippingName' => $checkout_data['shippingName'],
                'idShippingAddress' => $checkout_data['idShippingAddress'],
                'totalWeight' => $cart_data['totalWeight'],
                'amountProduct' => $cart_data['amountProduct'],
                'amountShipping' => $cart_data['amountShipping'],
                'amountDiscount' => $cart_data['amountDiscount'],
                'amountTax' => $cart_data['amountTax'],
                'amountFinal' => $cart_data['amountFinal'],
            ));
            
            // Create order items
            foreach ( $cart_data['items'] as $cartitem ):

                $orderitem = $orderitem_handler->create(array (
                    'oid' => $order->oid,
                    'name' => $cartitem->name,
                    'description' => $cartitem->description,
                    'idProduct' => $cartitem->idProduct,
                    'rate' => $cartitem->rate,
                    'quantity' => $cartitem->quantity,
                    'unitWeight' => $cartitem->unitWeight,
                    'totalWeight' => $cartitem->totalWeight,
                    'amountProduct' => $cartitem->amountProduct,
                    'amountDiscount' => $cartitem->amountDiscount,
                    'amountShipping' => $cartitem->amountShipping,
                    'amountTax' => $cartitem->amountTax,
                    'amountFinal' => $cartitem->amountFinal,
                    'dtCreated' => date('Y-m-d H:i:s'),
                ));
                $orderitem->save();

            endforeach;
            
            // Reset checkout data
            $checkout_data = NULL;
            
            // Mark all cart items for deletion
            foreach ($cart_data['items'] as $cartitem):
                $cartitem->status = -2;
                $cartitem->save();
            endforeach;
            
            $dbo->commit();
            
        } catch(\Exception $ex) {
            
            $dbo->rollBack();
            throw $ex;
            
        }
        
        return $order;
        
    }
    
    /**
     * Redirects the user to the appropriate checkout step
     */
    public static function _jumpToStep() {
        
        $auth_user = \Natty::getUser();
        $cartitem_handler = \Natty::getHandler('commerce--cartitem');
        $cart_data = CheckoutHelper::readUserCartData();
        $response = \Natty::getResponse();
        
        $key = CheckoutHelper::initCheckoutData();
        $checkout_data =& $_SESSION[$key];
        
        // Cart cannot be empty
        if ( 0 === sizeof($cart_data['items']) ):
            $location = \Natty::url('commerce/cartitems');
            $response->redirect($location);
        endif;
        
        // Account setup
        if ( $auth_user->uid <= 0 ):
            \Natty\Console::error('In order to place your order, please sign in using your username and password. If you do not have an account, you can create one in under a minute.');
            $location = \Natty::url('checkout/account-setup');
            $response->redirect($location);
        endif;
        
        // Address setup
        if (!$checkout_data['idShippingAddress'] ||
                !$checkout_data['idBillingAddress'] ||
                !$checkout_data['shippingName'] ||
                !$checkout_data['billingName']):
            \Natty\Console::error('Please provide your addresses in order to checkout.');
            $location = \Natty::url('checkout/address-setup');
            $response->redirect($location);
        endif;
        
    }
    
}