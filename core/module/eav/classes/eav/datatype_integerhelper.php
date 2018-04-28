<?php

namespace Module\Eav\Classes\Eav;

use Module\Eav\Classes\AttributeObject;
use Module\Eav\Classes\AttrinstObject;
use Module\Eav\Classes\DatatypeHelperAbstract;

abstract class Datatype_IntegerHelper
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'eav--integer';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['input']['method'] = 'system--input';
        $output['input']['size'] = 10;
        return $output;
    }
    
    public static function onBeforeInstanceSave(AttrinstObject $attrinst) {
        if ( !$attrinst->isNew )
            $attrinst->readUnchanged();
        parent::onBeforeInstanceSave($attrinst);
    }
    
    public static function onInstanceSave(AttrinstObject $attrinst) {
        
        parent::onInstanceSave($attrinst);
        
        // Look for changes in column size
        if ( !$attrinst->isNew ):
            
            if ( !static::storageInUse($attrinst) ):
            
                $attrinst_unchanged = $attrinst->readUnchanged();

                if ( $attrinst_unchanged->settings['input']['size'] != $attrinst->settings['input']['size'] ):

                    $tablename = $attrinst->settings['storage']['tablename'];

                    $table_definition = self::getStorageTableDefinition($attrinst);
                    $column_definition = $table_definition['columns']['value'];
                    $column_definition['name'] = 'value';

                    $schema_helper = \Natty::getDbo()->getSchemaHelper();
                    $schema_helper->alterColumn($tablename, 'value', $column_definition);

                endif;
                
            endif;
            
        endif;
        
    }
    
    public static function getStorageTableDefinition(AttrinstObject $attrinst) {
        
        $column_length = isset ($attrinst->settings['input']['size'])
                ? $attrinst->settings['input']['size'] : 10;
        
        $definition = parent::getStorageTableDefinition($attrinst);
        $definition['columns']['value'] = array (
            'type' => 'int',
            'length' => $column_length,
        );
        return $definition;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        parent::handleSettingsForm($data);
        
        $form =& $data['form'];
        $attribute =& $data['attribute'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                $form->items['input']['_data']['settings.input.size'] = array (
                    '_widget' => 'input',
                    '_label' => 'Length',
                    '_description' => 'Maximum number of digits which this attribute can contain.',
                    '_default' => $attribute->settings['input']['size'],
                    '_validators' => array (
                        array ('natty_validate_number', array ('maxValue' => 20)),
                    ),
                );

                if ( self::storageInUse($attribute) ):
                    $form->items['input']['_data']['settings.input.size']['_ignore'] = 1;
                    $form->items['input']['_data']['settings.input.size']['readonly'] = 1;
                endif;
                
                break;
        endswitch;
        
                
        
    }
    
}