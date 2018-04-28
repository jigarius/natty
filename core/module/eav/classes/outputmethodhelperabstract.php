<?php

namespace Module\Eav\Classes;

use Module\Eav\Classes\AttrinstObject;

abstract class OutputMethodHelperAbstract
extends \Natty\Uninstantiable {
    
    public static function buildOutput(array $values, array $options) {}
    
    public static function attachSettingsForm(AttrinstObject $attrinst, $view_mode = 'default') {
        
        $output_settings = $attrinst->settings['output']['default'];
        if ( isset ($attrinst->settings['output'][$view_mode]) )
            $output_settings = $attrinst->settings['output'][$view_mode];
        
        $widgets = array (
            '_widget' => 'container',
            '_label' => 'View mode: ' . $view_mode,
            '_data' => array (),
        );
        $widgets['_data']['label'] = array (
            '_widget' => 'dropdown',
            '_label' => 'Label',
            '_options' => array (
                'above' => 'Above',
                'inline' => 'Inline',
                'hidden' => 'Hidden',
            ),
            '_default' => $output_settings['label'],
            'class' => array ('widget-small'),
        );
        
        return $widgets;
        
    }
    
    public static function getDefaultSettings() {
        return array (
            'label' => 'above',
        );
    }
    
}