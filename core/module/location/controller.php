<?php

namespace Module\Location;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
}