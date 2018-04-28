<?php

namespace Natty\Core;

/**
 * Description of filepermexception
 *
 * @author Jigar M
 */
class FilePermException
extends \Exception {
    
    public function __construct($message = NULL, $code = NULL, $previous = NULL) {
        if ( is_null($message) )
            $message = 'Inadequate read/write permissions';
        parent::__construct($message, $code, $previous);
    }
    
}
