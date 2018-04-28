<?php

namespace Module\Media\Logic;

class Backend_ImageStyleController {
    
    public static function pageManage() {
        
        // Load libraries
        $istyle_handler = \Natty::getHandler('media--imagestyle');
        
        // List head
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => '', 'class' => array ('context-menu'))
        );
        
        // List items
        $istyle_coll = $istyle_handler->read(array (
            'ail' => \Natty::getOutputLangId()
        ));
        
        // List body
        $list_body = array ();
        foreach ( $istyle_coll as $istyle ):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $istyle->name . '</div>'
                    .'<div class="prop-description">Internal name: ' . $istyle->iscode . '</div>';
            $row['context-menu'] = $istyle->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/media/image-style/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode = 'create', $entity = NULL) {
        
        return 'Create and edit image styles here. Code pending.';
        
    }
    
}