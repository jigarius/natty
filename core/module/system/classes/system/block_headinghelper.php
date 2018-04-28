<?php

namespace Module\System\Classes\System;

class Block_HeadingHelper
extends \Module\System\Classes\BlockHelperAbstract {
    
    public static function handleSettingsForm(&$data) {
        
        parent::handleSettingsForm($data);
        
        $form = &$data['form'];
        switch ($form->getStatus()):
            case 'prepare':
                
                $form->items['default']['_data']['heading']['placeholder'] = 'Headings are not allowed for this type of module.';
                $form->items['default']['_data']['heading']['disabled'] = 1;
                
                break;
        endswitch;
        
    }
    
    public static function buildOutput(array $settings) {
        
        $output = array (
            '_data' => array (),
        );
        
        // Read heading
        $heading = \Natty::getResponse()->attribute('title');
        if ($heading)
            $output['_data'] = '<h1 id="heading">' . $heading . '</h1>';
        
        return $output;
        
    }
    
}