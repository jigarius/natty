<?php

namespace Module\Media\Classes\Eav;

use \Module\Eav\Classes\AttrinstObject;
use \Module\Eav\Classes\OutputMethodHelperAbstract;

class OutputMethod_FilenameHelper
extends OutputMethodHelperAbstract {
    
    public static function buildOutput(array $values, array $options) {
        
        $output = '';
        
        $site_base = \Natty::readSetting('system--siteBase');
        
        foreach ( $values as $vno => $value ):
            $output .= '<div class="attr-value omid-filename">';
            switch ( $options['link'] ):
                case 'file':
                    $output .= '<a href="' . $site_base . '/files/' . $value['location'] . '/' . $value['name'] . '" target="_blank">' . $value['name'] . '</a>';
                    break;
                default:
                    $output .= $value['name'];
                    break;
            endswitch;
            $output .= '</div>';
        endforeach;
        
        return $output;
        
    }
    
    public static function attachSettingsForm(AttrinstObject $attrinst, $view_mode = 'default') {
        
        $output_settings = array ();
        if ( isset ($attrinst->settings['output'][$view_mode]) )
            $output_settings = $attrinst->settings['output'][$view_mode];
        $output_settings = self::getDefaultSettings() + $output_settings;
        
        $widgets = parent::attachSettingsForm($attrinst, $view_mode);
        
        $widgets['_data']['link'] = array (
            '_widget' => 'options',
            '_label' => 'Link',
            '_description' => 'Whether the filename should be linked to the file.',
            '_options' => array (
                '' => 'No link',
                'file' => 'Link to file',
            ),
            '_default' => $output_settings['link'],
            'class' => array ('options-inline'),
        );
        
        return $widgets;
        
    }
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['link'] = 'file';
        return $output;
    }
    
}