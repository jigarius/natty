<?php

namespace Natty\Core;

class ControllerException
extends \Exception {
    
    public function __construct( $message = NULL, $code = NULL ) {
        
        // If only one integer has been passed, it is the error code
        if ( 1 == func_num_args() && is_numeric($message) ):
            $code = $message;
            $message = null;
        endif;
        
        parent::__construct($message, $code);
        
    }
    
}