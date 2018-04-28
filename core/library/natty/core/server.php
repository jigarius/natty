<?php

namespace Natty\Core;

/**
 * Natty Server Object
 * @author JigaR Mehta | Greenpill Productions
 * @package natty
 */
class Server
extends \Natty\SingletonObject {
    
    public function getDocumentRoot() {
        return $_SERVER['DOCUMENT_ROOT'];
    }
    
    public function getAddress() {
        return $_SERVER['SERVER_ADDR'];
    }
    
    public function getRemoteAddress() {
        return $_SERVER['REMOTE_ADDR'];
    }
    
}