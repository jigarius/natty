<?php

namespace Module\Cms;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onSystemBlockDeclare(&$data) {
        include 'declare/system-block.php';
    }
    
    public static function onSystemEntitytypeDeclare(&$data) {
        
        $data['cms--content'] = array (
            'name' => 'Content',
            'isAttributable' => TRUE,
            'isTranslatable' => TRUE,
        );
        
    }
    
}