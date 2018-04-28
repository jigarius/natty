<?php

namespace Module\Eav\Classes\Eav;

use Module\Eav\Classes\DatatypeHelperAbstract;

/**
 * Not complete
 */
abstract class Datatype_EntityreferenceHelper 
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'eav--entityreference';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['input']['etid'] = NULL;
        $output['input']['egid'] = NULL;
        $output['input']['idKey'] = NULL;
        $output['input']['groupKey'] = NULL;
        return $output;
    }
    
    public static function onBeforeAttributeSave(AttributeObject $attribute) {
        
        parent::onBeforeAttributeSave($attribute);
        
        if ( $attribute->settings['input']['etid'] ) :
            $etype = \Natty::getEntity('system--entitytype', $attribute->settings['input']['etid']);
            $etype_handler = \Natty::getHandler($etype->etid);
            $attribute->settings['input']['idKey'] = $etype_handler->getKey('id');
            $attribute->settings['input']['groupKey'] = $etype_handler->getKey('group');
        endif;
        
    }
    
    public static function getStorageTableDefinition(\Module\Eav\Classes\AttrinstObject $attrinst) {
        
        $definition = parent::getStorageTableDefinition($attrinst);
        
        $definition['columns']['value'] = array (
            'type' => 'varchar',
            'length' => 128,
        );
        
        return $definition;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        parent::handleSettingsForm($data);
        
        $form =& $data['form'];
        $attribute =& $data['attribute'];
        
        $input_settings =& $attribute->settings['input'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                // Read entity-types
                $etype_coll = \Natty::getPackage('module', 'system')->readEntityTypes();
                $etype_options = array ();
                foreach ( $etype_coll as $etype ):
                    $etype_options[$etype->etid] = $etype->name;
                endforeach;

                // Read entity-groups
                $egroup_options = array ();
                if ( $input_settings['etid'] ):
                    $etype_handler = \Natty::getHandler($input_settings['etid']);
                    $egroup_data = $etype_handler->getEntityGroupData();
                    foreach ( $egroup_data as $egid => $eg_data ):
                        $egroup_options[$egid] = $eg_data['name'];
                    endforeach;
                endif;

                $form->items['input']['_data']['settings.input.etid'] = array (
                    '_widget' => 'dropdown',
                    '_label' => 'Entity Type',
                    '_description' => 'The target entity type for the reference.',
                    '_options' => $etype_options,
                    '_default' => $input_settings['etid'],
                    'placeholder' => '',
                    'required' => 1,
                );
                $form->items['input']['_data']['settings.input.egid'] = array (
                    '_widget' => 'options',
                    '_label' => 'Entity Group',
                    '_options' => $egroup_options,
                    '_description' => 'The entity group for the reference. If left blank, all groups would be used.',
                    '_default' => $input_settings['egid'],
                    'multiple' => 1,
                );

                if ( isset ($attribute->aiid) && self::storageInUse($attribute) ):
                    $form->items['input']['_data']['settings.input.etid']['readonly'] = 1;
                    $form->items['input']['_data']['settings.input.egid']['readonly'] = 1;
                endif;
                
                break;
        endswitch;
        
    }
    
}