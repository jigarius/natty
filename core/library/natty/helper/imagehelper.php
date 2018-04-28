<?php

namespace Natty\Helper;

/**
 * ImageHelper
 * @author Jigar M
 */
class ImageHelper 
extends \Natty\Uninstantiable {
    
    /**
     * Converts an image file to another format.
     * @param string $source Source filename with source extension
     * @param string $dest Destination filename with final extension
     */
    public static function reformat($source, $dest = NULL) {
        
        $source_info = filesize($source);
        fphp_exit($source_info);
        
    }
    
    public static function resize($source, array $options) {
        
        $options = array_merge(array (
            'width' => FALSE,
            'height' => FALSE,
            'stretch' => FALSE,
            'destination' => FALSE,
        ), $options);
        extract($options, EXTR_PREFIX_ALL, 'opt');
        
        // Validate options
        if ( !$opt_width || !$opt_height )
            trigger_error('Expected options "width" and "height" to be non-zero numbers!', E_USER_ERROR);
        
        // Prepare source image data
        $src_image = $source;
        if ( !is_resource($src_image) && !$src_image = self::createFromFile($source) )
            return FALSE;
        
        // Read source information
        $source_info = array (
            'width' => imagesx($src_image),
            'height' => imagesy($src_image),
        );
        
        // Determine output dimensions
        $dest_width = $opt_width;
        $dest_height = $opt_height;
        
        // Get new dimensions
        if ( !$opt_stretch ):
            // Determine aspect ratio
            $aratio = $source_info['width'] / $source_info['height'];
            // Fit height first
            $dest_height = $dest_width / $aratio;
            // Fit width if required
            if ( $dest_height > $opt_height )
                $dest_width = $dest_height * $aratio;
        endif;
        
        // Resize the image
        $dest_image = imagecreatetruecolor($dest_width, $dest_height);
        imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $source_info['width'], $source_info['height']);
        
        // Save to file (resized)
        if ( $opt_destination )
            return self::saveToFile($dest_image, $opt_destination);
        
        // Return raw image resource
        return $dest_image;
        
    }
    
    public static function createFromFile($filename) {
        $info = getimagesize($filename);
        switch ( $info[2] ):
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                return imagecreatefromjpeg($filename);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
            default:
                die ('Unsupported image format!');
        endswitch;
    }
    
    /**
     * 
     * @param resource $image
     * @param string $filename
     * @param options $options
     */
    public static function saveToFile($image, $filename, array $options = array ()) {
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch ( strtolower($extension) ):
            case 'gif':
                return imagepng($image, $filename);
            case 'png':
                return imagegif($image, $filename);
            default:
                $options = array_merge(array (
                    'quality' => 70
                ), $options);
                return imagejpeg($image, $filename, $options['quality']);
        endswitch;
        
    }
    
}
