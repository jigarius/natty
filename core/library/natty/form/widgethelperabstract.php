<?php

namespace Natty\Form;

/**
 * Base definition for all form widget implementations. Every WidgetHelper
 * definition must be extended from this class.
 */
abstract class WidgetHelperAbstract {
    
    /**
     * Prepares the widget definition with required attributes and validates
     * the definition.
     * @param array $definition Widget definition
     * @throws \RuntimeException If the definition is missing any required 
     * attributes.
     */
    public static function prepare(array &$definition) {
        
        // Pre-process the definition
        $definition = array_merge(array (
            '_module' => 'system',
            '_widget' => NULL,
            '_container' => array (),
            '_display' => 1,
            '_label' => FALSE,
            '_description' => FALSE,
            '_default' => isset ($definition['multiple']) ? array () : FALSE,
            '_validators' => array (),
            '_errors' => array (),
            '_prepared' => TRUE,
            '_prefix' => NULL,
            '_suffix' => NULL,
            'class' => array (),
            'required' => 0,
            'readonly' => 0,
            'disabled' => 0,
            'required' => 0,
        ), $definition);
        
        // Set default value
        if ( !isset ($definition['_value']) )
            $definition['_value'] = $definition['_default'];
        
        // Add container classnames
        $definition['_container'][] = 'form-item';
        $definition['_container'][] = 'form-' . $definition['_module'] . '-' . $definition['_widget'];
        if ( $definition['required'] )
            $definition['_container'][] = 'form-item-required';
        if ( $definition['readonly'] )
            $definition['_container'][] = 'form-item-readonly';
        if ( $definition['disabled'] )
            $definition['_container'][] = 'form-item-disabled';
        if ( $definition['name'] )
            $definition['_container'][] = 'form-item-' . str_replace('.', '-', $definition['name']);
        
        // Add rendering data
        $handler = WidgetHelper::getHelperClass($definition['_module'], $definition['_widget']);
        $definition['_render'] = 'form_item';
        
    }
    
    /**
     * Value getter for the widget. To ensure considering POST values,
     * the setter must be called first.
     * @param array $definition Widget definition
     * @return mixed Value assigned to the widget or false if no 
     * value is available for return.
     */
    public static function getValue(array &$definition) {
        
        // Value is to be ignored?
        if ( isset ($definition['_ignore']) )
            return;
        
        return $definition['_value'];
        
    }
    
    /**
     * Value setter for the widget. Sets values from POST data or default
     * value, whichever seems applicable.
     * @param array $definition Widget definition
     */
    public static function setValue(array &$definition) {
        
        // Widget has no name? Do nothing!
        if ( !isset ($definition['name']) )
            return;
        
        // Widget is readonly? Ignore post value!
        if ( $definition['readonly'] || $definition['disabled'] )
            return;
        
        // Determine POST value
        if ( sizeof($_POST) > 0 ):
            $post = natty_array_get($_POST, $definition['name']);
            if ( is_null($post) ) {
                if ( isset ($definition['multiple']) )
                    $definition['_value'] = array ();
            }
            else {
                $definition['_value'] = $post;
            }
        endif;
        
    }
    
    public static function preRender(array &$definition) {
        
        // Add error class to container
        if ( sizeof($definition['_errors']) )
            $definition['_container'][] = 'n-state-error';
        
    }
    
    public static function renderPrefix($definition) {
        
        $output = static::renderLabel($definition);
        if ( isset ($definition['_prefix']) )
            $output .= '<span class="prefix">' . $definition['_prefix'] . '</span> ';
        return $output;
        
    }
    
    public static function renderSuffix($definition) {
        
        $output = '';
        if ( isset ($definition['_suffix']) )
            $output .= ' <span class="suffix">' . $definition['_suffix'] . '</span>';
        $output .= static::renderMessages($definition);
        return $output;
        
    }
    
    /**
     * Widget renderer. Renders the widget as per definition.
     * @param array $definition Widget definition rarray
     * @return string Markup
     */
    public static function render(array $definition) {
        
        if ( !isset ($definition['_prepared']) )
            self::prepare ($definition);
        self::preRender($definition);
        
        // Render label and messages
        $prefix_markup = self::renderPrefix($definition);
        $suffix_markup = self::renderSuffix($definition);
        
        // Render widget
        $widget_markup = natty_render($definition);
        
        // Return markup
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $prefix_markup . $widget_markup . $suffix_markup
            . '</div>';
        
    }
    
    public static function renderLabel(array $definition) {
        
        $markup = '';
        
        if ( !$definition['_label'] )
            return $markup;
        
        $markup .= '<label' . (isset ($definition['id']) ? ' for="' . $definition['id'] . '"' : '') . '>' 
                . $definition['_label'] 
                . ( $definition['required'] ? ' <span class="asterisk">*</span>' : '' )
                . '</label>';
        
        return $markup;
        
    }
    
    public static function renderMessages(array $definition) {
        
        $markup = '';
        
        // Render hint
        if ( $definition['_description'] ):
            $markup .= '<div class="hint">' . $definition['_description'] . '</div>';
        endif;
        
        // Render errors
        if ( sizeof($definition['_errors']) ):
            foreach ( $definition['_errors'] as $message ):
                $markup .= '<div class="error">' . $message . '</div>';
            endforeach;
        endif;
        
        // Wrap it in a wrapper
        if ( $markup )
            $markup = '<div class="messages">' . $markup . '</div>';
        
        return $markup;
        
    }
    
    public static function validate(&$definition, $form) {
        
        // Get the value for the field
        $value = $definition['_value'];

        // See if the field is required
        $empty = FALSE;
        if ( $definition['required'] ):

            if ( isset ($definition['multiple']) ) {
                if ( !$definition['_value'] ):
                    $definition['_errors'][] = 'This field cannot be left empty.';
                    $empty = TRUE;
                endif;
            }
            else {
                if ( 0 === strlen($definition['_value']) ):
                    $definition['_errors'][] = 'This field cannot be left empty.';
                    $empty = TRUE;
                endif;
            }

        endif;

        // If field has a value then invoke validators
        if ( !$empty && sizeof($definition['_validators']) ):
            foreach ( $definition['_validators'] as $entry ):
                $validation_callback = $entry[0];
                $validation_args = $entry;
                $validation_args[0] = $definition['_value'];
                $validation_args[] = $definition;
                $validation_args[] = $form;
                $validation_messages = call_user_func_array($validation_callback, $validation_args);
                if ( TRUE !== $validation_messages ):
                    if ( is_array($validation_messages) )
                        $definition['_errors'] = array_merge($definition['_errors'], $validation_messages);
                    else
                        $definition['_errors'][] = $validation_messages;
                endif;
                unset ($validation_callback, $validation_args, $validation_messages);
            endforeach;
        endif;
        
        return sizeof($definition['_errors']) ? FALSE : TRUE;
        
    }
    
}