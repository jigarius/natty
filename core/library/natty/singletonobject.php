<?php

namespace Natty;

abstract class SingletonObject
extends StdClass {
    
    /**
     * The singleton instances
     * @var array
     */
    protected static $instances = array ();
    
    protected function __construct() {}
    
    final protected function __clone() {
        throw new \LogicException('Cannot clone a singleton object!');
    }
    
    /**
     * Returns a singleton instance of the class
     */
    public static function &getInstance() {
        
        // If an instance does not exist, create one
        $classname = get_called_class();
        if ( !isset (self::$instances[$classname]) )
            self::$instances[$classname] = new $classname();
        
        return self::$instances[$classname];
        
    }
    
}