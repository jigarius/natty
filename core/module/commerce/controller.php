<?php

namespace Module\Commerce;

class Controller
extends \Natty\Core\PackageObject {
    
    public function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public function onSystemEmailDeclare() {
        include 'declare/system-email.php';
    }
    
    public function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public function onCatalogProductViewFormHandle(&$data) {
        
        $form =& $data['form'];
        $entity =& $data['entity'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                $form->items['quantity'] = array (
                    '_label' => 'Quantity',
                    '_widget' => 'input',
                    '_default' => 1,
                    'type' => 'number',
                    'min' => 1,
                    'max' => 999,
                    'maxlength' => 3,
                    'class' => array ('size-small'),
                    'required' => 1,
                );
                
                // Price would come last
                $fi_price = $form->items['rate'];
                unset ($form->items['rate']);
                $form->items['rate'] = $fi_price;
                
                $form->actions['add2cart'] = array (
                    '_label' => 'Add to cart',
                );
                break;
            case 'validate':
                
                break;
            case 'process':
                
                $form_data = $form->getValues();
                
                $cartitem_handler = \Natty::getHandler('commerce--cartitem');
                $cartitem = $cartitem_handler->create($form_data);
                $cartitem->save();
                
                // Redirect
                $message = natty_text('The item {{ @name }} was added to your cart. {{ t1 }}Click here{{ /t1 }} to review items in your cart and checkout.', array (
                    'name' => $entity->name,
                    't1' => '<a href="' . \Natty::url('cart/items') . '">',
                    '/t1' => '</a>',
                ));
                \Natty\Console::success($message);
                
                break;
        endswitch;
        
    }
    
    public static function onCommerceCarriertypeDeclare(&$data) {
        include 'declare/commerce-carriertype.php';
    }
    
    public static function onPayrecCommerceOrderTranComplete(&$tran) {
        
        // If transaction was not successful, do nothing.
        if ($tran->status <= 0)
            return;
        
        // Save payment data into the order
        $order = \Natty::getEntity('commerce--order', $tran->contextId);
        $order->amountPaid += $tran->amount;
        
        // Mark order as paid, if not done so
        if ($order->amountPaid >= $order->amountFinal):
            if ($order->idActualStatus == Classes\TaskstatusHandler::ID_AWAITING_PAYMENT || $order->idActualStatus == Classes\TaskstatusHandler::ID_VERIFYING_PAYMENT):
                $order->idActualStatus = Classes\TaskstatusHandler::ID_PENDING;
                $order->idVisibleStatus = Classes\TaskstatusHandler::ID_PENDING;
            endif;
        endif;
        
        $order->save();
        
        // Send an email acknowledging the receipt
        $user = \Natty::getEntity('people--user', $order->idCustomer);
        
        $email = \Natty::getEntity('system--email', 'commerce--order paid');
        $email->send(array (
            'recipientUser' => $user,
            'data' => array (
                'customer' => $user,
                'tran' => $tran,
                'order' => $order,
            ),
        ));
        
    }
    
}