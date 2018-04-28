<?php

namespace Module\Taxonomy;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemEntitytypeDeclare(&$data) {
        
        $data['taxonomy--term'] = array (
            'name' => 'Taxonomy term',
            'isAttributable' => TRUE,
        );
        
    }
    
}