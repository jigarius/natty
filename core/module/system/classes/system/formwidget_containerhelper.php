<?php

namespace Module\System\Classes\System;

use Natty\Form\WidgetHelper as WidgetHelper;

class FormWidget_ContainerHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare(array &$definition) {
        
        parent::prepare($definition);
        
        if ( !isset ($definition['_type']) )
            $definition['_type'] = 'fieldset';
        
        if ( !isset ($definition['_data']) )
            $definition['_data'] = array ();
        
        if ( !isset ($definition['_label']) )
            $definition['_label'] = FALSE;
        
        // Prepare all fields inside the container
        foreach ( $definition['_data'] as $item_name => &$item_definition ):
            
            if ( !is_numeric($item_name) )
                $item_definition['name'] = $item_name;
            
            \Natty\Form\WidgetHelper::prepare($item_definition);
            
            unset ($item_definition);
            
        endforeach;
        
    }
    
    public static function getValue(array &$definition) {
        
        $output = array ();
        
        foreach ( $definition['_data'] as $item_name => $item_definition ):
            
            $item_value = WidgetHelper::getValue($item_definition);
            if ( !is_null($item_value) )
                natty_array_set($output, $item_name, $item_value);
            
        endforeach;
        
        return $output;
        
    }
    
    public static function setValue(array &$definition) {
        
        foreach ( $definition['_data'] as $item_name => &$item_definition ):
            WidgetHelper::setValue($item_definition);
            unset ($item_definition);
        endforeach;
        
    }
    
    public static function validate(&$definition, $form) {
        $output = TRUE;
        foreach ( $definition['_data'] as $item_key => &$item_definition ):
            $item_valid = \Natty\Form\WidgetHelper::validate($item_definition, $form);
            if ( FALSE === $item_valid )
                $output = FALSE;
        endforeach;
        return $output;
    }

    public static function render( array $definition ) {
        
        // Container classes have no use
        unset ($definition['_container'], $definition['name']);
        
        // Display description
        if ( $definition['_description'] ):
            $desc_rarray = array (
                '_render' => 'markup',
                '_markup' => '<div class="container-description">' . $definition['_description'] . '</div>'
            );
            array_unshift($definition['_data'], $desc_rarray);
        endif;
        
        // Render a fieldset?
        if ( 'fieldset' == $definition['_type'] ) {
            
            $definition['_render'] = 'element';
            $definition['_element'] = 'fieldset';
            
            // Display the legend
            if ( $definition['_label'] ):
                $legend_rarray = array (
                    '_render' => 'markup',
                    '_markup' => '<legend>' . $definition['_label'] . '</legend>'
                );
                array_unshift($definition['_data'], $legend_rarray);
            endif;
            
        }
        // Will have other stuff later.
        else {
            natty_debug('Container can currently only be a fieldset.');
        }
        
        return natty_render($definition);
        
    }
    
}