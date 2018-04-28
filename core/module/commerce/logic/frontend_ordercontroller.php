<?php

namespace Module\Commerce\Logic;

class Frontend_OrderController {
    
    public static function pageManage() {
        
        // Load dependencies
        $auth_user = \Natty::getUser();
        $order_handler = \Natty::getHandler('commerce--order');
        $orderitem_handler = \Natty::getHandler('commerce--orderitem');
        
        // Build query
        $query = $order_handler->getQuery()
                ->addColumn('ts18.name visibleStatusName')
                ->addJoin('inner', '%__commerce_taskstatus_i18n ts18', '{ts18}.{tsid} = {order}.{idActualStatus} AND {ts18}.{ail} = :ail')
                ->addSimpleCondition('order.idCreator', ':uid');
        
        // Read relevant orders
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
                'uid' => $auth_user->uid,
            ),
            'fetch' => array ('entity', $order_handler->getEntityTypeId()),
        ));
        
        // Read relevant order items
        $order_coll = $list_data['items'];
        $order_id_coll = array_keys($order_coll);
        $orderitem_coll = $orderitem_handler->read(array (
            array ('AND', array ('{orderitem}.{oid}', 'IN', $order_id_coll)),
        ));
        
        // Pre-process orders
        foreach ($order_coll as &$order):
            $order->url = \Natty::url('backend/commerce/orders/' . $order->oid);
            unset ($order);
        endforeach;
        
        // Read relevant product data
        $product_id_coll = array ();
        foreach ($orderitem_coll as $oiid => $orderitem):
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
        
        // Attach orderitems to orders
        $orderitem_data = array ();
        foreach ($orderitem_coll as $oiid => $orderitem):
            
            $orderitem->image = FALSE;
            if ( isset ($pimage_data[$orderitem->idProduct]) ):
                $pimage = $pimage_data[$orderitem->idProduct];
                $orderitem->image = \Natty::instancePath($pimage['location'], 'base');
            endif;
            
            $orderitem->productUrl = \Natty::url('catalog/product/' . $orderitem->idProduct);
            $orderitem->links = $orderitem->call('buildFrontendLinks');
            
            // Arrange by order id
            if (!isset ($orderitem_data[$orderitem->oid]))
                $orderitem_data[$orderitem->oid] = array ();
            $orderitem_data[$orderitem->oid][$oiid] = $orderitem;
            
        endforeach;
        
        // Prepare response
        $output = array ();
        $output[] = array (
            '_render' => 'template',
            '_template' => 'module/commerce/tmpl/frontend-order-list.tmpl',
            '_data' => array (
                'orderColl' => $order_coll,
                'orderitemColl' => $orderitem_data,
            ),
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
}