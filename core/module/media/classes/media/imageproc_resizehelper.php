<?php

namespace Module\Media\Classes\Media;

use Natty\Helper\ImageHelper;
use \Module\Media\Classes\ImageProcHelperAbstract;

class Imageproc_ResizeHelper
extends ImageProcHelperAbstract {
    
    public static function applyToImage(&$imagedata, array $options = array()) {
        
        $options = array_merge(self::getDefaultSettings(), $options);
        
        list ($opt_width, $opt_height) = explode('x', $options['resolution']);
        
        $output = ImageHelper::resize($imagedata, array (
            'width' => $opt_width,
            'height' => $opt_height,
        ));
        
        $imagedata = $output;
        
    }
    
    public static function getDefaultSettings() {
        return array (
            'resolution' => FALSE,
        );
    }
    
}