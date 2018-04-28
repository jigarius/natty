<?php

namespace Module\Catalog\Logic;

class ProductTypeController {
    
    public static function pageManage() {
        
        // Load dependencies
        $ptype_handler = \Natty::getHandler('catalog--producttype');
        $query = $ptype_handler->getQuery();
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'fetch' => array ('entity', 'catalog--producttype'),
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
        ));
        
        // List body
        $list_body = array ();
        foreach ($list_data['items'] as $ptype):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $ptype->name . ($ptype->isLocked ? ' (Locked)' : '') . '</div>';
            $row['context-menu'] = $ptype->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/catalog/product-types/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
}