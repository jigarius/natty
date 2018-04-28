<?php

namespace Module\System\Classes\System;

class FormWidget_TextareaHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['rows']) )
            $definition['rows'] = 5;
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Determine value to display
        $definition['_data'] = $definition['_value'];
        
        // Render label and messages
        $label_markup = self::renderLabel($definition);
        $message_markup = self::renderMessages($definition);
        
        // Render widget
        $definition['_element'] = 'textarea';
        $widget_markup = natty_render_element($definition);
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $label_markup . $widget_markup . $message_markup
            . '</div>';
        
    }
    
}