<?php

namespace Module\System\Classes\System;

class FormWidget_RteHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        $definition = natty_array_merge_nested(array (
            '_toolbar' => 'basic',
            'data-ui-init' => array ('rte'),
            'rows' => 20,
        ), $definition);
        
        // Include editor plugin
        \Natty::getResponse()->addScript(array (
            '_key' => 'ckeditor',
            'src' => NATTY_BASE . 'core/plugin/ckeditor/ckeditor.js'
        ));
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Determine value to display
        $definition['_data'] = $definition['_value'];
        
        // Determine toolbar
        $definition['data-rte-toolbar'] = $definition['_toolbar'];
        
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