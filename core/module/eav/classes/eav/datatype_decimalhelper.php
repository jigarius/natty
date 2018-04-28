<?php

namespace Module\Eav\Classes\Eav;

use Module\Eav\Classes\AttributeObject;
use Module\Eav\Classes\AttrinstObject;
use Module\Eav\Classes\DatatypeHelperAbstract;
use Natty\Form\FormObject;

abstract class Datatype_DecimalHelper
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'eav--decimal';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['method'] = 'system--input';
        $output['size'] = 10;
        $output['scale'] = 2;
        return $output;
    }
    
    public static function onBeforeInstanceSave(AttrinstObject $attrinst) {
        if ( !$attrinst->isNew )
            $attrinst->readUnchanged();
        parent::onBeforeInstanceSave($attrinst);
    }
    
    public static function onInstanceSave(AttrinstObject $attrinst) {
        
        parent::onInstanceSave($attrinst);
        
        // Modification
        if ( !$attrinst->isNew ):
            
            // See if we have any data in the storage
            if ( !self::storageInUse($attrinst) ):

                // Look for changes in column size
                $attrinst_old = $attrinst->readUnchanged();
                
                // See if definition was changed
                $definition_changed = FALSE;
                if ( $attrinst_old->settings['input']['size'] != $attrinst->settings['input']['size'] )
                    $definition_changed = TRUE;
                if ( $attrinst_old->settings['input']['scale'] != $attrinst->settings['input']['scale'] )
                    $definition_changed = TRUE;
                
                // Update definition
                if ( $definition_changed ):
                    
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
        
        $column_size = isset ($attrinst->settings['input']['size'])
                ? $attrinst->settings['input']['size'] : 10;
        $column_scale = isset ($attrinst->settings['input']['scale'])
                ? $attrinst->settings['input']['scale'] : 2;
        
        // Scale must be smaller than length
        if ( $column_scale > $column_size )
            $column_scale = 0;
        
        $definition = parent::getStorageTableDefinition($attrinst);
        
        $definition['columns']['value'] = array (
            'type' => 'decimal',
            'length' => $column_size . ',' . $column_scale,
        );
        
        return $definition;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        parent::handleSettingsForm($attribute, $form);
        
        $form =& $data['form'];
        $attribute =& $data['attribute'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                $form->items['input']['_data']['settings.input.size'] = array (
                    '_widget' => 'input',
                    '_label' => 'Length',
                    '_description' => 'Maximum number of digits allowed. Example: 2.025 makes it 4 digits.',
                    '_default' => $attribute->settings['input']['size'],
                    '_validators' => array (
                        array ('natty_validate_number', array ('maxValue' => 20)),
                    ),
                );
                $form->items['input']['_data']['settings.input.scale'] = array (
                    '_widget' => 'input',
                    '_label' => 'Places After Decimal',
                    '_description' => 'Number of digits allowed after decimal. Example: 2.025 makes 3 places after decimal.',
                    '_default' => $attribute->settings['input']['scale'],
                    '_validators' => array (
                        array ('natty_validate_number', array ('maxValue' => 5)),
                    ),
                );

                if ( self::storageInUse($attribute) ):
                    $form->items['input']['_data']['settings.input.size']['_ignore'] = 1;
                    $form->items['input']['_data']['settings.input.size']['readonly'] = 1;
                    $form->items['input']['_data']['settings.input.scale']['_ignore'] = 1;
                    $form->items['input']['_data']['settings.input.scale']['readonly'] = 1;
                endif;
                
                break;
        endswitch;
        
    }
    
}