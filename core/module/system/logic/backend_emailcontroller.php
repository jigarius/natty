<?php

namespace Module\System\Logic;

class Backend_EmailController {
    
    public static function pageManage() {
        
        // Rebuild route declaration
        $email_handler = \Natty::getHandler('system--email');
        $list_data = $email_handler->read(array (
            'language' => \Natty::getOutputLangId(),
        ));
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List body
        $list_body = array ();
        foreach ( $list_data as $email ):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $email->name . '</div>'
                . '<div class="prop-description">Internal name: ' . $email->eid . '</div>';
            $row['context-menu'] = array ();
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output = [];
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
}