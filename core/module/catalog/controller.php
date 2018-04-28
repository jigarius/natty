<?php

namespace Module\Catalog;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onSystemEntitytypeDeclare(&$data) {
        $data['catalog--product'] = array (
            'name' => 'Product',
            'isAttributable' => 1,
        );
        $data['catalog--producttype'] = array (
            'name' => 'Product type',
        );
    }
    
    public static function taxonomyTermUri($term) {
        
        $output = FALSE;
        
        switch ( $term->gcode ):
            case 'catalog-categories':
                $output = \Natty::url('catalog/category/' . $term->tid);
                break;
        endswitch;
        
        return $output;
        
    }
    
}