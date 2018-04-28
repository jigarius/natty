<?php

namespace Module\System\Classes\System;

class FormWidget_MultientryHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare( array &$definition ) {
        
        // Must be multivalue
        $definition['multiple'] = 'multiple';
        
        // Must specify a sample widget
        if ( !isset ($definition['_sample']) ):
            $definition['_sample'] = array (
                '_widget' => 'markup',
                '_markup' => 'Invalid configuration.',
            );
        endif;
        
        // Prepare data pocket
        $definition['_data'] = array ();
        
        parent::prepare($definition);
        
    }
    
//    public static function setValue(array &$definition) {
//        
//        // No name? Do nothing
//        if ( !isset ($definition['name']) )
//            return;
//        
//        parent::setValue($definition);
//        $values = parent::getValue($definition);
//        
//    }
    
    public static function preRender(array &$definition) {
        
        // Repeat the widget to show multiple values
        $definition['_data'] = array ();
        foreach ( $definition['_value'] as $vno => $value ):
            
            if ( !is_numeric($vno) )
                continue;
            
            $instance = $definition['_sample'];
            $instance['_default'] = $value;
            $instance['name'] = $definition['name'] . '.';
            
            $definition['_data'][] = $instance;
            
        endforeach;
        
        // Need to add a new value holder?
        if ( isset ($definition['_value']['another']) ):
            $instance = $definition['_sample'];
            $instance['name'] = $definition['name'] . '.';
            $definition['_data'][] = $instance;
        endif;
        
        parent::preRender($definition);
        
    }
    
    public static function render( array $definition ) {
        
        static::preRender($definition);
        
        $markup = '';
        
        // Render label and messages
        $label_markup = self::renderLabel($definition);
        $message_markup = self::renderMessages($definition);
        
        // Render existing values
        $table_head = array (
            array ('_data' => 'OOA', 'width' => 100),
            array ('_data' => 'Value'),
        );
        $table_body = array ();
        foreach ( $definition['_data'] as $vno => $instance ):
            $table_body[] = array (
                '<input name="" maxlength="3" value="' . $vno . '" />',
                \Natty\Form\WidgetHelper::render($instance)
            );
        endforeach;
        
        // Render blank instance
        if ( 0 == sizeof($definition['_data']) ):
            $table_body []= array (
                '',
                \Natty\Form\WidgetHelper::render($definition['_sample'])
            );
        endif;
        
        // "Add Another" button
        $table_body[] = array (
            '',
            \Natty\Form\WidgetHelper::render(array (
                '_widget' => 'button',
                '_label' => 'Add another',
                'name' => $definition['name'] . '.another',
                'type' => 'submit',
            ))
        );
        
        // Render table
        $widget_markup = natty_render_table(array (
            '_head' => $table_head,
            '_body' => $table_body,
            'class' => array (
                'n-table', 'n-table-border-outer', 'n-table-striped', 'multientry-widget-table'
            )
        ));
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $label_markup . $widget_markup . $message_markup
            . '</div>';
        
    }
    
}