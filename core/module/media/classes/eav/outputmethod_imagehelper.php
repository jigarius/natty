<?php

namespace Module\Media\Classes\Eav;

use \Module\Eav\Classes\AttrinstObject;
use \Module\Eav\Classes\OutputMethodHelperAbstract;
use Natty\Helper\FileHelper;

class OutputMethod_ImageHelper
extends OutputMethodHelperAbstract {
    
    public static function buildOutput(array $values, array $options) {
        
        $output = '';
        
        $site_base = \Natty::readSetting('system--siteBase');
        $rebuild_time = \Natty::readSetting('system--rebuildTime');
        
        foreach ( $values as $vno => $value ):
            
            $value_path = dirname($value['location']);
            $value['markup'] = '<img src="' . FileHelper::instancePath($value_path, 'base') . '/' . $value['nameWoe'] . '-' . $options['isid'] . '.' . $value['extension'] . '?' . $rebuild_time . '" alt=""></a>';
            
            $output .= '<div class="attr-value">';
            switch ( $options['link'] ):
                case 'entity':
                    $output .= '<a href="' . $options['entity']->getUri() . '">' . $value['markup'] . '</a>';
                    break;
                case 'file':
                    $output .= '<a href="' . FileHelper::instancePath($value['location'], 'base') . '" target="_blank">' . $value['markup'] . '</a>';
                    break;
                default:
                    $output .= $value['markup'];
                    break;
            endswitch;
            $output .= '</div>';
            
        endforeach;
        
        return $output;
        
    }
    
    public static function attachSettingsForm(AttrinstObject $attrinst, $view_mode = 'default') {
        
        $output_settings = $attrinst->settings['output']['default'];
        if ( isset ($attrinst->settings['output'][$view_mode]) )
            $output_settings = $attrinst->settings['output'][$view_mode];
        
        $widgets = parent::attachSettingsForm($attrinst, $view_mode);
        
        $widgets['_data']['isid'] = array (
            '_widget' => 'dropdown',
            '_label' => 'Style',
            '_options' => array (
                'orig' => 'Original',
                'thumb' => 'Thumbnail',
            ),
            '_default' => $output_settings['isid'],
        );
        $widgets['_data']['link'] = array (
            '_widget' => 'dropdown',
            '_label' => 'Linked',
            '_description' => 'Whether the filename should be linked to the file.',
            '_options' => array (
                '' => 'No link',
                'file' => 'Link to file',
                'entity' => 'Link to entity',
            ),
            '_default' => $output_settings['link'],
        );
        
        return $widgets;
        
    }
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['label'] = 'hidden';
        $output['isid'] = 'thumb';
        $output['link'] = 'file';
        return $output;
    }
    
}