<?php

namespace Module\System\Classes\System;

class FormWidget_OptionsHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['_options']) )
            $definition['_options'] = array ();
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Generate label and messages
        $label_markup = self::renderLabel($definition);
        $message_markup = self::renderMessages($definition);
        
        // Determine value to display
        $selections = isset ($definition['multiple'])
                ? (array) $definition['_value'] : array ($definition['_value']);
        
        // Generate widget
        $definition['_element'] = 'div';
        $definition['_data'] = '';
        $definition['class'][] = 'option-container';
        
        // Determine default option attributes
        $option_default = array (
            'readonly' => $definition['readonly'],
            'disabled' => $definition['disabled'],
        );
        if ( isset ($definition['multiple']) ) {
            $option_default['name'] = $definition['name'] . '.';
            $option_default['type'] = 'checkbox';
            
        }
        else {
            $option_default['name'] = $definition['name'];
            $option_default['type'] = 'radio';
        }
        
        // Generate markup options
        $cur_level = -1;
        $cur_index = -1;
        $tot_options = sizeof($definition['_options']);
        foreach ( $definition['_options'] as $option_index => $option_rarray ):
            
            $cur_index++;
            
            // Convert string options to array
            if ( !is_array($option_rarray) )
                $option_rarray = array ('_label' => $option_rarray);
            if ( isset ($option_rarray['_data']) ):
                $option_rarray['_label'] = $option_rarray['_data'];
                unset ($option_rarray['_data']);
            endif;
            
            // Determine value
            if ( !isset ($option_rarray['value']) )
                $option_rarray['value'] = $option_index;
            
            // Determine option id
            if ( !isset ($option_rarray['id']) )
                $option_rarray['id'] = str_replace('.', '-', $definition['name']) . '-option-' . $option_rarray['value'];
            
            // Merge with defaults
            $option_rarray = array_merge($option_default, $option_rarray);
            
            // Mark selected options
            if ( in_array($option_rarray['value'], $selections) )
                $option_rarray['checked'] = 'checked';
            
            // Render the option
            $option_rarray['_element'] = 'input';
            $option_markup = 
                    '<div class="option">'
                        . ( isset ($option_rarray['level']) ? str_repeat('<span class="n-indent"></span>', $option_rarray['level']) : '' )
                        . natty_render_element($option_rarray)
                        . ' <label for="' . $option_rarray['id'] . '">' . $option_rarray['_label'] . '</label>'
                        . ( isset ($option_rarray['_description']) ? '<div class="description">' . $option_rarray['_description'] . '</div>' : '' )
                    . '</div>';
            
            $definition['_data'] .= $option_markup;
            
        endforeach;
        
        // Render select element
        unset ($definition['name']);
        $widget_markup = natty_render_element($definition);
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $label_markup . $widget_markup . $message_markup
            . '</div>';
        
    }
    
}