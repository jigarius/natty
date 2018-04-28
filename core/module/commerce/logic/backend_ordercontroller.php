<?php

namespace Module\Commerce\Logic;

class Backend_OrderController {
    
    public static function pageManage($uid = NULL) {
        
        // Load dependencies
        $auth_user = \Natty::getUser();
        $order_handler = \Natty::getHandler('commerce--order');
        
        if ('self' === $uid)
            $uid = $auth_user->uid;
        
        $admin_mode = !$uid && $auth_user->can('commerce--manage any order entities');
        
        // Build query
        $query = $order_handler->getQuery()
                ->addColumn('ts18.name visibleStatusName')
                ->addJoin('inner', '%__commerce_taskstatus_i18n ts18', '{ts18}.{tsid} = {order}.{idActualStatus} AND {ts18}.{ail} = :ail');
        
        if ($uid)
            $query->addSimpleCondition('idCreator', (int) $uid);
        
        // List head
        $list_head = array (
            array ('_data' => 'Order date', 'class' => array ('size-medium')),
            array ('_data' => 'Order code', 'class' => array ('size-medium')),
            array ('_data' => 'Billing name'),
            array ('_data' => 'Shipping name'),
            array ('_data' => 'Amount', 'class' => array ('size-small', 'n-ta-ri')),
            array ('_data' => 'Status', 'class' => array ('size-small')),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List body
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
            'fetch' => array ('entity', $order_handler->getEntityTypeId()),
        ));
        
        // Read relevant order items
        $list_body = array ();
        foreach ($list_data['items'] as $order):
            
            $row = array ();
        
            $row['dtCreated'] = natty_format_datetime($order->dtCreated, array (
                'format' => 'datetime',
            ));
            $row['title'] = $order->ocode;
            $row['billTo'] = $order->billingName;
            $row['shipTo'] = $order->shippingName;
            $row['value'] = array (
                '_data' => natty_format_money($order->amountFinal, array (
                    'currency' => $order->idCurrency,
                )),
                'class' => array ('n-ta-ri'),
            );
            $row['status'] = $order->visibleStatusName;
            $row['context-menu'] = $order->call('buildBackendLinks');
            
            $list_body[] = $row;
        
        endforeach;
        
        
        // Prepare response
        $output = array ();
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageView($order) {
        
        // Load dependencies
        $orderitem_handler = \Natty::getHandler('commerce--orderitem');
        $tstatus_handler = \Natty::getHandler('commerce--taskstatus');
        
        $admin_mode = \Natty::getUser()->can('commerce--manage any order entities');
        $output = [];
        
        // Read relevant order items
        $order->items = $orderitem_handler->readByKeys(array (
            'oid' => $order->oid
        ));
        
        // Read relevant product data
        $product_id_coll = array ();
        foreach ($order->items as $oiid => $orderitem):
            $product_id_coll[$orderitem->idProduct] = $orderitem->idProduct;
        endforeach;
        
        $pimage_data = \Natty::getDbo()->read('%__eav_data_catalog_product_mediaImage', array (
            'conditions' => array (
                array ('AND', array ('eid', 'IN', $product_id_coll)),
                array ('AND', array ('vno', '=', 0)),
                array ('AND', array ('ail', '=', ':ail')),
            ),
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
        ));
        $pimage_data = natty_array_reindex($pimage_data, 'eid');
        
        // Attach images to order items
        foreach ($order->items as $oiid => &$orderitem):
            
            $orderitem->image = FALSE;
            if ( isset ($pimage_data[$orderitem->idProduct]) ):
                $pimage = $pimage_data[$orderitem->idProduct];
                $orderitem->image = \Natty::instancePath($pimage['location'], 'base');
            endif;
            
            $orderitem->productUrl = \Natty::url('catalog/product/' . $orderitem->idProduct);
            $orderitem->links = $orderitem->call('buildFrontendLinks');
            
            unset ($orderitem);
            
        endforeach;
        
        // Read addresses
        $order->shippingAddress = \Natty::getEntity('location--useraddress', $order->idShippingAddress);
        $order->billingAddress = \Natty::getEntity('location--useraddress', $order->idBillingAddress);
        
        // Read order status
        $order->visibleStatus = $tstatus_handler->readById($order->idVisibleStatus);
        
        // Read creator information
        $order->customer = \Natty::getEntity('people--user', $order->idCustomer);
        
        // Read transaction history
        $tran_coll = array ();
        if ($mod_payrec = \Natty::getPackage('module', 'payrec')):
            
            $tran_handler = \Natty::getHandler('payrec--tran');
            $tran_coll = $tran_handler->readByKeys(array (
                'contextType' => 'commerce--order',
                'contextId' => $order->oid,
            ), array (
                'conditions' => array (
                    array ('AND', '{status} >= 0'),
                ),
                'ordering' => array (
                    'status' => 'desc',
                    'dtCreated' => 'desc',
                ),
                'limit' => 5,
            ));
            
        endif;
        
        // Read shipment history
        $shipment_coll = \Natty::getHandler('commerce--shipment')->readByKeys(array (
            'oid' => $order->oid,
        ));
        
        // Build a form for the admin
        if ($admin_mode):
            
            // Prepare form
            $form = new \Natty\Form\FormObject(array (
                'id' => 'commerce-order-view',
            ), array (
                'etid' => 'commerce--order',
                'entity' => $order,
            ));
            $form->items['default']['_label'] = 'Update order';
            
            $tstatus_opts = $tstatus_handler->readOptions(array (
                array ('key' => array (
                    'status' => 1,
                ))
            ));
            $form->items['default']['_data']['idVisibleStatus'] = array (
                '_label' => 'Status',
                '_widget' => 'dropdown',
                '_options' => $tstatus_opts,
                '_default' => $order->idVisibleStatus,
            );
            
            $form->actions['save'] = array (
                '_label' => 'Save',
            );
            
            $form->onPrepare();
            
            // Validate form
            if ($form->isSubmitted()):
                
                $form->onValidate();
                
            endif;
            
            // Process form
            if ($form->isValid()):
                
                if ($form->isSubmitted('save')):
                    
                    $form_data = $form->getValues();
                    
                    $order->idVisibleStatus = $form_data['idVisibleStatus'];
                    $order->save();
                    
                    \Natty\Console::success();
                    
                    $form->onProcess();
                
                endif;
                
            endif;
            
            // Add form to output
            $output['form'] = $form->getRarray();
            
        endif;
        
        // Prepare response
        $output['order'] = array (
            '_render' => 'template',
            '_template' => 'module/commerce/tmpl/order.tmpl',
            '_data' => array (
                'order' => $order,
                'tranColl' => $tran_coll,
                'shipmentColl' => $shipment_coll,
            ),
        );
        return $output;
        
    }
    
}