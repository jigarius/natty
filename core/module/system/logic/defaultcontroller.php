<?php

namespace Module\System\Logic;

class DefaultController {
    
    public static function pageDefault() {
        return 'Ok';
    }
    
    public static function pageAjax() {
        
        $request = \Natty::getRequest();
        
        // Determine callback
        $callback = $request->getString('call');
        $callback_parts = explode('--', $callback, 2);
        if ( 2 !== sizeof($callback_parts) )
            \Natty::error(400);
        
        // Determine callback module
        $module_code = $callback_parts[0];
        if ( !\Natty::getPackage('module', $module_code) )
            \Natty::error(400);
        $callback_classname = '\\Module\\' . ucfirst($module_code) . '\\Logic\\AjaxController';
        
        // Determine callback method
        if ( !$callback_parts[1] )
            \Natty::error(400);
        $callback_method = 'action' . natty_strtocamel($callback_parts[1], TRUE);
        if ( !method_exists($callback_classname, $callback_method) )
            \Natty::error(400);
        
        // Prepare response
        $output = array (
            '_status' => TRUE,
            '_message' => NULL,
        );
        $callback_classname::$callback_method($output);
        
        echo json_encode($output);
        exit;
        
    }
    
    public static function pageError($code, $message) {
        
        // Load dependencies
        $response = \Natty::getResponse();

        // If an error message was also set
        if ( !empty ($message) ):
            \Natty\Console::error($message, array (
                'heading' => 'Error'
            ));
        endif;

        // Send headers
        switch ( $code ):
            case 400:
                $response->attribute('title', '400 Bad Request');
                $response->header('HTTP/1.0 400 Bad Request', TRUE, 400);
                break;
            case 403:
                $response->attribute('title', '403 Unauthorized Access');
                $response->header('HTTP/1.0 403 Unauthorized Access', TRUE, 403);
                break;
            case 404:
                $response->attribute('title', '404 Not Found');
                $response->header('HTTP/1.0 404 Not Found', TRUE, 404);
                break;
            default:
                $response->attribute('title', '500 Internal Error');
                $response->header('HTTP/1.0 404 Not Found', TRUE, 404);
                break;
        endswitch;
        
        // Render page
        $output = array (
            '_render' => 'template',
            '_template' => array (
                'module/system/tmpl/error.tmpl',
                'module/system/tmpl/error.' . $code . '.tmpl',
            ),
        );
        
        return $output;
        
    }
    
}