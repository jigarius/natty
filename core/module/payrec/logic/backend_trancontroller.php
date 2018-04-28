<?php

namespace Module\Payrec\Logic;

class Backend_TranController {
    
    public static function pageView($tran) {
        
        // Load dependencies
        $response = \Natty::getResponse();
        $output = array ();
        
        // Render transaction
        $output['tran'] = $tran->render(array (
            'page' => TRUE,
        ));
        
        // Invoke payment method
        $pmethod = \Natty::getHandler('payrec--method')->readById($tran->mid);
        $pmethod_helper = $pmethod->helper;
        $pmethod_helper::attachView($tran, $output);
        
        // Prepare output
        $page_title = natty_text($tran->name, $tran->variables);
        $response->attribute('title', $page_title);
        
        return $output;
        
    }
    
}