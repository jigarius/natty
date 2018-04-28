<?php

namespace Module\System\Classes\System;

class FormWidget_InputHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function getValue(array &$definition) {
        
        $output = parent::getValue($definition);
        
        // Checkbox returns value only when it is checked
        if ( 'checkbox' == $definition['type'] ):
            $output = ( $definition['value'] == $definition['_value'] )
                ? $definition['value'] : FALSE;
        endif;
        
        return $output;
        
    }
    
    public static function setValue(array &$definition) {
        
        parent::setValue($definition);
        
        // Convert value to timestamp
        if ( $definition['_timestamp'] && $definition['_value'] ):
            if ( !is_numeric($definition['_value']) )
                $definition['_value'] = strtotime($definition['_value']);
        endif;
        
    }
    
    public static function prepare( array &$definition ) {
        
        parent::prepare($definition);
        
        $definition = natty_array_merge_nested(array (
            'type' => 'text',
            '_timestamp' => FALSE,
        ), $definition);
        
        switch ( $definition['type'] ):
            case 'date':
            case 'time':
            case 'datetime':
                $definition['data-ui-init'][] = $definition['type'] . '-picker';
                break;
            case 'number':
                $definition['class'][] = 'n-ta-ri';
                break;
            case 'checkbox':
                if ( !isset ($definition['value']) )
                    $definition['value'] = 1;
                break;
        endswitch;
        
    }
    
    public static function validate(&$definition, $form) {
        
        $value = $definition['_value'];
        
        switch ( $definition['type'] ):
            case 'number':
                if ($value && !is_numeric($value))
                    $definition['_errors'][] = 'Please enter a valid number.';
                if (isset ($definition['min']) && $value < $definition['min'])
                    $definition['_errors'][] = 'Please enter a value greater than ' . $definition['min'] . '.';
                if (isset ($definition['max']) && $value > $definition['max'])
                    $definition['_errors'][] = 'Please enter a value less than ' . $definition['max'] . '.';
                break;
            case 'color':
                if ($value && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value))
                    $definition['_errors'][] = 'Please enter a valid hex color code like #000000.';
                break;
        endswitch;
        
        // Validate minimum length
        if ( isset ($definition['minlength']) ):
            $char_diff = $definition['minlength'] - strlen($value);
            if ( $char_diff > 0 ):
                $definition['_errors']['minlength'] = 'The value must be at least ' . $definition['minlength'] . ' characters long. The value you entered is ' . $char_diff . ' characters less than that.';
            endif;
        endif;
        
        // Validate maximum length
        if ( isset ($definition['maxlength']) ):
            $char_diff = strlen($value) - $definition['maxlength'];
            if ( $char_diff > 0 ):
                $definition['_errors']['maxlength'] = 'The value must be at most ' . $definition['minlength'] . ' characters long. The value you entered is ' . $char_diff . ' characters more than that.';
            endif;
        endif;
        
        return parent::validate($definition, $form);
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Add type class to container
        $definition['_container'][] = 'input-' . $definition['type'];
        
        // Determine value to display
        switch ( $definition['type'] ):
            case 'radio':
            case 'checkbox':
                if ( $definition['value'] == $definition['_value'] )
                    $definition['checked'] = 'checked';
                break;
            case 'date':
                if ( $definition['_timestamp'] ) {
                    if ( $definition['_value'] )
                        $definition['value'] = date('Y-m-d', $definition['_value']);
                }
                else {
                    $definition['value'] = $definition['_value'];
                }
                break;
            case 'time':
                if ( $definition['_timestamp'] && $definition['_value'] )
                    $definition['value'] = date('H:i:00', $definition['_value']);
                break;
            case 'datetime':
                if ( $definition['_timestamp'] && $definition['_value'] )
                    $definition['value'] = date('Y-m-d H:i:00', $definition['_value']);
                break;
            default:
                $definition['value'] = $definition['_value'];
                break;
        endswitch;
        
        // Render label and messages
        $prefix_markup = self::renderPrefix($definition);
        $suffix_markup = self::renderSuffix($definition);
        
        // For checkboxes, add a hidden input field to capture empty state
        if ( 'checkbox' === $definition['type'] ):
            $prefix_markup .= '<input' . natty_render_attributes(array (
                'name' => $definition['name'],
                'type' => 'hidden',
            )) . '/>';
        endif;
        
        $widget_markup = '<input' . natty_render_attributes($definition) . '/>';
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $prefix_markup . $widget_markup . $suffix_markup
            . '</div>';
        
    }
    
}