<?php

namespace Natty\Form;

abstract class WidgetHelper {
    
    protected static $helpers = array ();
    
    /**
     * Returns the handler class for this widget.
     * @param string $module
     * @param string $widget
     * @return string Classname for the widget handler
     * @throws \RuntimeException If the widget handler is not found.
     */
    public static function getHelperClass( $module, $widget ) {
        $key = $module . '--' . $widget;
        if ( !isset (self::$helpers[$key]) ):
            $classname = '\\Module\\' . ucfirst($module) . '\\Classes\\System\\FormWidget_' . ucfirst($widget) . 'Helper';
            if ( !class_exists($classname) )
                throw new \RuntimeException('Widget type "' . $module . '--' . $widget . '" is not supported.');
            self::$helpers[$key] = $classname;
        endif;
        return self::$helpers[$key];
    }
    
    /**
     * Finalizes necessary properties for form widget definition.
     * @param array $definition
     * @throws \RuntimeException
     */
    public static function prepare(array &$definition) {
        
        // Validate definition
        if ( !isset ($definition['_module']) )
            $definition['_module'] = 'system';
        if ( !isset ($definition['_widget']) ):
            $definition['_widget'] = FALSE;
            return;
        endif;
        
        $handler = self::getHelperClass($definition['_module'], $definition['_widget']);
        $handler::prepare($definition);
        
        $definition['_prepared'] = TRUE;
        
    }
    
    /**
     * Gets the value assigned to a widget.
     * @param array $definition Widget definition
     * @return mixed Value assigned to the widget or false if no 
     * value is available for return.
     */
    public static function getValue(array &$definition) {
        
        if ( !$definition['_widget'] )
            return;
        
        $handler = self::getHelperClass($definition['_module'], $definition['_widget']);
        return $handler::getValue($definition);
        
    }
    
    /**
     * Assigns a value to a widget.
     * @param array $definition Widget definition
     */
    public static function setValue(array &$definition) {
        
        if ( !$definition['_widget'] )
            return;
        
        $handler = self::getHelperClass($definition['_module'], $definition['_widget']);
        $handler::setValue($definition);
        
    }
    
    /**
     * Renders the widget in its given state.
     * @param array $definition Widget definition
     * @return string Markup
     */
    public static function render(array $definition) {
        
        if ( !$definition['_widget'] )
            return;
        
        if ( !isset ($definition['_prepared']) )
            self::prepare($definition);
        
        $handler = self::getHelperClass($definition['_module'], $definition['_widget']);
        return $handler::render($definition);
        
    }
    
    /**
     * Calls widget validator.
     * @param array $definition Widget definition
     * @param type $form Form object
     */
    public static function validate(&$definition, $form) {
        
        if ( !$definition['_widget'] )
            return;
        
        $handler = self::getHelperClass($definition['_module'], $definition['_widget']);
        return $handler::validate($definition, $form);
        
        
    }
    
}