<?php

namespace Module\System\Classes\System;

class FormWidget_InameHelper
extends FormWidget_InputHelper {
    
    public static function prepare( array &$definition ) {
        
        $definition['type'] = 'text';
        
        // Add validations
        $definition['_validators'][] = array (
            'natty_validate_string', array (
                'minLength' => 4
            ),
        );
        $definition['_validators'][] = array (
            array (__CLASS__, 'validate')
        );
        
        // Determine other options
        if ( !isset ($definition['_iname']) )
            $definition['_iname'] = array ();
        $definition['_iname'] = array_merge(array (
            'base' => FALSE,
            'regexPattern' => NATTY_REGEX_INAME,
            'regexMessage' => 'Value must contain only alphabets, hyphens and numbers.',
            'conflictCallback' => NULL,
        ), $definition['_iname']);
        
        if ( !isset ($definition['_label']) )
            $definition['_label'] = 'Internal Name';
        
        if ( !isset ($definition['_description']) )
            $definition['_description'] = 'This would be used as an internal name for use in code.';
        
        parent::prepare($definition);
        
    }
    
    public static function setValue(array &$definition) {
        
        // Generate value based on another field
        if ( $definition['_iname']['base'] && 0 === strlen($definition['_value']) ):
            $base_value = natty_array_get($_POST, $definition['_iname']['base']);
            if ( $base_value ):
                $definition['_value'] = natty_slug($base_value);
                return;
            endif;
        endif;
        
        parent::setValue($definition);
        
    }


    public static function validate(&$definition, $form) {
        
        // Value must match iname pattern
        if ( $definition['_iname']['regexPattern'] ):
            if ( !preg_match($definition['_iname']['regexPattern'], $definition['_value']) )
                return $definition['_iname']['regexMessage'];
        endif;
        
        // If value has changed, call the conflict callback
        if ( $definition['_value'] != $definition['_default'] ):
            
            // Conflict callback
            if ( $definition['_iname']['conflictCallback'] ) {

                $conflict_callback = $definition['_iname']['conflictCallback'];
                if ( $conflict_callback($definition['_value']) )
                    return 'The internal name is already in use.';

            }
            
        endif;
        
        return TRUE;
        
    }
    
}