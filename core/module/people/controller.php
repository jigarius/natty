<?php

namespace Module\People;

class Controller
extends \Natty\Core\PackageObject {
    
    public function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public function onSystemBlockDeclare(&$data) {
        include 'declare/system-block.php';
    }
    
    public function onSystemEmailDeclare(&$data) {
        include 'declare/system-email.php';
    }
    
    public function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public function onSystemBeforeRender(&$data) {
        
        $user = \Natty::getUser();
        
        $response = \Natty::getResponse();
        $response->flags[] = ($user->uid > 0)
                ? 'is-auth' : 'not-auth';
        
    }
    
}