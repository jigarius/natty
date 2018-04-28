<?php

namespace Module\Listing\Logic;

class Frontend_ListController {
    
    public static function pageViewList($lid, $did) {
        
        // Load dependencies
        $list = \Natty::getEntity('listing--list', $lid);
        $display = $list->readVisibility($did);
        $entity_handler = \Natty::getHandler($list->settings['etid']);
        
        // List query
        $query = $entity_handler->getQuery();
        $query_params = array ();
        
        // List head
        $list_head = array ();
        if ( $lang_key = $entity_handler->getKey('language') )
            $query_params[$lang_key] = \Natty::getOutputLangId();
        
        // Setup filters
        foreach ( $display['filterData'] as $key => $definition ):
            
            $query->addSimpleCondition($definition['tableAlias'] . '.' . $definition['columnName'], ':filter_' . $key, $definition['method']);
            $query_params['filter_' . $key] = $definition['operand'];
            
        endforeach;
        
        // Setup sorts
        foreach ( $display['filterData'] as $key => $definition ):
            
            $query->orderBy($definition['tableAlias'] . '.' . $definition['columnName'], $definition['method']);
            
        endforeach;
        
        // List body
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $paging_helper->setParameters($list_head);
        $list_data = $paging_helper->execute(array (
            'parameters' => $query_params,
            'fetch' => array ('entity', $entity_handler->getEntityTypeId()),
        ));
        
        $list_body = array ();
        foreach ( $list_data['items'] as $item ):
            
            $item = array (
                '_render' => 'entity',
                '_entity' => $item,
                '_options' => array (
                    'viewMode' => $display['renderType'],
                    'page' => FALSE,
                    'links' => $display['renderLinks'],
                ),
            );
            $list_body[] = $item;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['list'] = array (
            '_render' => 'list',
            '_element' => 'div',
            '_items' => $list_body,
        );
        
        return $output;
        
    }
    
}