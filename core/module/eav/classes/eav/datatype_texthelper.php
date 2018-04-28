<?php

namespace Module\Eav\Classes\Eav;

use Module\Eav\Classes\AttributeObject;
use Module\Eav\Classes\AttrinstObject;
use Module\Eav\Classes\DatatypeHelperAbstract;

abstract class Datatype_TextHelper
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'eav--text';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['input']['method'] = 'system--textarea';
        return $output;
    }
    
    public static function getStorageTableDefinition(AttrinstObject $attrinst) {
        
        $definition = parent::getStorageTableDefinition($attrinst);
        $definition['columns']['value'] = array (
            'type' => 'text'
        );
        return $definition;
        
    }
    
}