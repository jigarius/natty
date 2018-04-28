<?php

namespace Module\Contact;

class Controller
extends \Natty\Core\PackageObject {
    
    public function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
}
