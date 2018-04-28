<?php

namespace Module\Eav\Classes\Eav;

use Natty\Form\FormObject;
use Module\Eav\Classes\AttrinstObject;

class InputMethod_InputHelper
extends \Module\Eav\Classes\InputMethodHelperAbstract {
    
    public static function attachValueForm(AttrinstObject $attrinst, FormObject &$form) {
        
        $widget = array (
            '_widget' => 'input',
            '_label' => $attrinst->name,
            '_description' => $attrinst->description,
            'maxlength' => $attrinst->settings['input']['size'],
        );
        
        switch ( $attrinst->dtid ):
            case 'eav--integer':
            case 'eav--decimal':
                $widget['type'] = 'number';
                break;
        endswitch;
        
        if ( $attrinst->settings['input']['required'] )
            $widget['required'] = TRUE;
        
        return $widget;
        
    }
    
}