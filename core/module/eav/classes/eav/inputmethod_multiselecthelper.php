<?php

namespace Module\Eav\Classes\Eav;

use Natty\Form\FormObject;
use Module\Eav\Classes\AttrinstObject;

class InputMethod_MultiSelectHelper
extends \Module\Eav\Classes\InputMethodHelperAbstract {
    
    public static function attachValueForm(AttrinstObject $attrinst, FormObject &$form) {
        
        $widget = array (
            '_widget' => 'dropdown',
            '_label' => $attrinst->name,
            '_description' => $attrinst->description,
            'data-ui-init' => array ('multiselect'),
            'data-multiselect-source' => \Natty::url('eav/attr-inst/' . $attrinst->aiid . '/read-suggestions'),
            'data-multiselect-data-value-field' => 'sid',
            'data-multiselect-max-selected-items' => $attrinst->nov,
        );
        
        if ( $attrinst->settings['input']['required'] )
            $widget['required'] = 1;
        
        return $widget;
        
    }
    
}