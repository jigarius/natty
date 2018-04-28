<?php

namespace Module\Easex;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemRouteDeclare(&$data) {
        
        $data['easex/dashmenu'] = array (
            'module' => 'easex',
            'heading' => 'Dashmenu',
            'contentCallback' => 'easex::DefaultController::pageDashMenu',
            'permArguments' => array (),
        );
        
    }
    
    public static function onSystemExecute() {
        
        $response = \Natty::getResponse();
        $auth_user = \Natty::getUser();
        
        $response->addScript(array (
            '_data' => 'var ModEasex = ModEasex || {}; ModEasex.showToolbar = ' . intval($auth_user->can('easex--view toolbar')) . ';',
        ));
        
    }
    
}