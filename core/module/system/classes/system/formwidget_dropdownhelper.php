<?php

namespace Module\System\Classes\System;

class FormWidget_DropdownHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['size']) )
            $definition['size'] = 1;
        
        // Prepare options
        if ( !isset ($definition['_options']) )
            $definition['_options'] = array ();
        
        // Store possible values for validation
        if ( !isset ($definition['_possibleValues']) )
            $definition['_possibleValues'] = array ();
        
        // Generate markup for other options
        foreach ( $definition['_options'] as $option_index => $option_rarray ):
            
            // Convert string options to array
            if ( !is_array($option_rarray) )
                $option_rarray = array ('_data' => $option_rarray);
            $option_rarray['_data'] = htmlentities($option_rarray['_data']);
            
            // Determine option value
            if ( !isset ($option_rarray['value']) )
                $option_rarray['value'] = $option_index;
            if ( is_array($definition['_possibleValues']) )
                $definition['_possibleValues'][] = $option_rarray['value'];
            
            $definition['_options'][$option_index] = $option_rarray;
            
        endforeach;
        
    }
    
    public static function validate(&$definition, $form) {
        
        // Determine selected values and convert it to an array
        $selections = array ();
        if ( $definition['_value'] ):
            $selections = isset ($definition['multiple'])
                ? $definition['_value'] : array ($definition['_value']);
        endif;
        
        // Values must be from amongst the possible values
        foreach ( $selections as $selection ):
            if ( is_array($definition['_possibleValues']) && !in_array($selection, $definition['_possibleValues']) ):
                natty_debug();
                $definition['_errors'];
                break;
            endif;
        endforeach;
        
        return parent::validate($definition, $form);
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Generate label and messages
        $prefix_markup = self::renderPrefix($definition);
        $suffix_markup = self::renderSuffix($definition);
        
        // Determine value to display
        $selections = isset ($definition['multiple'])
                ? $definition['_value'] : array ($definition['_value']);
        
        // Generate widget
        $definition['_element'] = 'select';
        $definition['_data'] = '';
        $definition['data-dropdown-selections'] = implode('; ', $selections);
        
        // Generate markup for empty option
        if ( isset ($definition['placeholder']) ):
            $definition['_data'] .= '<option value="">' . htmlentities($definition['placeholder']) . '</option>';
        endif;
        
        // Generate markup for other options
        foreach ( $definition['_options'] as $option_index => $option_rarray ):
            
            // Is this a tree of options? Then indent this option text!
            if ( isset ($option_rarray['level']) ):
                $option_rarray['_data'] = str_repeat('&nbsp;', $option_rarray['level']*2) . ' ' . $option_rarray['_data'];
                unset ($option_rarray['level']);
            endif;
            
            // Mark selected options
            if ( in_array($option_rarray['value'], $selections) )
                $option_rarray['selected'] = 'selected';
            
            // Render the option
            $option_rarray['_element'] = 'option';
            $definition['_data'] .= natty_render_element($option_rarray);
            
        endforeach;
        
        // Render select element
        $widget_markup = '';
        
        // Is the widget readonly? Then include a hidden input field for
        // holding the value and disable the select element
        if ( $definition['readonly'] ):
            
            foreach ( $selections as $vno => $value ):
                $widget_markup .= natty_render_element(array (
                    '_element' => 'input',
                    'type' => 'hidden',
                    'name' => isset ($definition['multiple'])
                        ? $definition['name'] . '.' . $vno : $definition['name'],
                    'value' => $value,
                ));
            endforeach;
            
            $definition['disabled'] = 1;
            
        endif;
        
        $widget_markup .= natty_render_element($definition);
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $prefix_markup . $widget_markup . $suffix_markup
            . '</div>';
        
    }
    
}