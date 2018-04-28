<?php

namespace Module\Payrec;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemEmailDeclare(&$data) {
        include 'declare/system-email.php';
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onPayrecMethodDeclare(&$data) {
        include 'declare/payrec-method.php';
    }
    
    public static function onSystemRebuildRegistry($data) {
        \Natty::getHandler('payrec--method')->rebuild();
    }
    
}