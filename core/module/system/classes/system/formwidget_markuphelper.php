<?php

namespace Module\System\Classes\System;

class FormWidget_MarkupHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['_markup']) )
            $definition['_markup'] = FALSE;
        
    }
    
    public static function render( array $definition ) {
        
        // Prepare widget markup
        $widget_markup = FALSE;
        if ( isset ($definition['_data']) )
            $widget_markup = natty_render($definition['_data']);
        if ( FALSE !== $definition['_markup'] )
            $widget_markup = $definition['_markup'];
        
        // If no markup is set, do not render
        if ( FALSE === $widget_markup )
            return;
        
        $prefix_markup = self::renderPrefix($definition);
        $suffix_markup = self::renderSuffix($definition);
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $prefix_markup . $widget_markup . $suffix_markup
            . '</div>';
        
    }
    
}