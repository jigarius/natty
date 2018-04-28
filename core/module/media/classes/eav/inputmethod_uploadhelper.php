<?php

namespace Module\Media\Classes\Eav;

use Module\Eav\Classes\InputMethodHelperAbstract;
use Natty\Form\FormObject;
use Module\Eav\Classes\AttrinstObject;
use Natty\Helper\FileHelper;

class InputMethod_UploadHelper
extends InputMethodHelperAbstract {
    
    public static function attachValueForm(AttrinstObject $attrinst, FormObject &$form) {
        
        // Form must support uploads
        $form->attributes['enctype'] = 'multipart/form-data';
        
        $widget = array (
            '_widget' => 'upload',
            '_label' => $attrinst->name,
            '_description' => $attrinst->description,
            '_extras' => array (
                'vid' => array (
                    '_widget' => 'input',
                    'type' => 'hidden',
                ),
                'description' => array (
                    '_widget' => 'input',
                    'maxlength' => 255,
                    'placeholder' => 'Description',
                )
            ),
        );
        
        // Constrain extensions
        if ( $attrinst->settings['input']['extensions'] )
            $widget['_extensions'] = explode(' ', $attrinst->settings['input']['extensions']);
        
        switch ( $attrinst->dtid ):
            case 'media--image':
                $widget['_valuePreviewCallback'] = array (__CLASS__, 'renderImagePreview');
                break;
        endswitch;
        
        if ( $attrinst->settings['input']['required'] )
            $widget['required'] = TRUE;
        
        return $widget;
        
    }
    
    public static function renderImagePreview(array $value) {
        
        if ( isset ($value['temporary']) )
            return '';
        
        $directory = dirname($value['location']);
        
        $preview_markup = 
                '<div class="preview-cont type-image">'
                    . '<a href="' . $value['_previewHref'] . '" target="_blank">'
                        . '<img src="' . FileHelper::instancePath($directory, 'base') . '/' . $value['nameWoe'] . '-thumb.' . $value['extension'] . '" alt="" />'
                    . '</a>'
                . '</div>';
        
        return $preview_markup;
        
    }
    
}