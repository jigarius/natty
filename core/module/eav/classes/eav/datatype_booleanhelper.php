<?php

namespace Module\Eav\Classes\Eav;

use Module\Eav\Classes\AttrinstObject;
use Module\Eav\Classes\DatatypeHelperAbstract;

abstract class Datatype_BooleanHelper
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'eav--boolean';
    
    public static function getStorageTableDefinition(AttrinstObject $attrinst) {
        
        $definition = parent::getStorageTableDefinition($attrinst);
        $definition['columns']['value'] = array (
            'type' => 'int',
            'length' => 1,
            'flags' => array ('unsigned'),
        );
        return $definition;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        parent::handleSettingsForm($data);
        
        $form =& $data['form'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                $form->items['input']['_data']['settings.input.nov'] = array (
                    'readonly' => 1,
                    '_value' => 1,
                );
                break;
        endswitch;
        
    }
    
}