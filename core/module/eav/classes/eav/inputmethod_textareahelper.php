<?php

namespace Module\Eav\Classes\Eav;

use Natty\Form\FormObject;
use Module\Eav\Classes\AttrinstObject;

class InputMethod_TextareaHelper
extends \Module\Eav\Classes\InputMethodHelperAbstract {
    
    public static function handleSettingsForm(array &$data) {
        
        parent::handleSettingsForm($data);
        
        $form =& $data['form'];
        $attribute =& $data['attribute'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                $settings = self::getDefaultSettings() + $attribute->settings['input'];
                
                $form->items['input']['_data']['settings.input.rows'] = array (
                    '_label' => 'Number of Rows',
                    '_widget' => 'input',
                    '_default' => $settings['rows'],
                    'maxlength' => 2,
                    'class' => array ('widget-small'),
                );
                $form->items['input']['_data']['settings.input.rte'] = array (
                    '_label' => 'Rich Text Editor',
                    '_widget' => 'options',
                    '_options' => array (
                        1 => 'On',
                        0 => 'Off',
                    ),
                    '_default' => $settings['rte'],
                    'class' => array ('options-inline'),
                );
                
                break;
        endswitch;
        
    }
    
    public static function attachValueForm(AttrinstObject $attrinst, FormObject &$form) {
        
        $widget = array (
            '_widget' => 'textarea',
            '_label' => $attrinst->name,
            '_description' => $attrinst->description,
            'rows' => 10,
        );
        
        $settings = array_merge(self::getDefaultSettings(), $attrinst->settings['input']);
        
        if ( $settings['rte'] )
            $widget['_widget'] = 'rte';
        
        if ( $settings['rows'] )
            $widget['rows'] = $settings['rows'];
        
        if ( $settings['required'] )
            $widget['required'] = TRUE;
        
        return $widget;
        
    }
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['rte'] = 0;
        $output['rows'] = 10;
        return $output;
    }
    
}