<?php

namespace Module\System\Classes\System;

class FormWidget_ButtonHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['type']) )
            $definition['type'] = 'submit';
        
        // Add classes
        $definition['class'][] = 'k-button';
        
    }
    
    public static function render( array $definition ) {
        
        $markup = '';
        
        // Extract button text
        $label = $definition['_label'];
        
        switch ( $definition['type'] ):
            case 'anchor':
                if ( $label )
                    $definition['_data'] = $label;
                $markup = natty_render_anchor($definition);
                break;
            default:
                if ( $label )
                    $definition['value'] = $label;
                $markup = '<input' . natty_render_attributes($definition) . '/>';
                break;
        endswitch;
        
        return $markup;
        
    }
    
}