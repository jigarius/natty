<?php

namespace Module\System\Classes\System;

class FormWidget_LanguageHelper
extends FormWidget_DropdownHelper {
    
    public static function prepare( array &$definition ) {
        
        if ( !isset ($definition['_options']) ):
            $language_handler = \Natty::getHandler('system--language');
            $definition['_options'] = $language_handler->readOptions(array (
                'key' => array ('status' => 1),
            ));
        endif;
        
        parent::prepare($definition);
        
    }
    
}