<?php

namespace Module\People\Logic;

class Backend_SettingsController {
    
    public static function pageDefault() {
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'people-settings-form',
        ));
        $form->items['default']['_label'] = 'Account settings';
        $form->items['default']['_data']['people--userRegiEnabled'] = array (
            '_label' => 'User registration',
            '_description' => 'Whether people can register themselves on your site.',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => \Natty::readSetting('people--userRegiEnabled'),
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $form_data = $form->getValues();
            foreach ( $form_data as $name => $value ):
                \Natty::writeSetting($name, $value);
            endforeach;
            
            \Natty\Console::success();
            
            $form->onProcess();
            
        endif;
        
        // Prepare output
        return $form->getRarray();
        
    }
    
}