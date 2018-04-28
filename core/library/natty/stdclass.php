<?php

namespace Natty;

defined('NATTY') or die;

/**
 * An interface to extend functionality of classes for older versions of PHP 
 * and to provide an easy interface for common tasks throughout the platform
 * 
 * @author JigaR Mehta
 */
class StdClass {

    /**
     * Grants read access for all properties in the class
     * @param string $name Name of the property
     * @return mixed Value of the property (if exists)
     */
    public function __get($name) {
        if ( isset ($this->$name) )
            return $this->$name;
    }

    /**
     * Sets the value for the given property in the object; If setters are
     * known, direct calls to set methods are preferred over overload calls
     * for performance reasons
     * @param string $name Name of the property
     * @param mixed $value Value for the property
     * @return mixed The value which was set
     */
    public function __set($name, $value) {
        $this->$name = $value;
        return $value;
    }

    /**
     * Returns the value for the specified class constant or static variable
     * @param string Name of the constant or class variable
     * @return mixed Value of the property
     */
    public function getStaticVar($name) {
        $code = 'return ' . $this->getClass() . '::' . $name . ';';
        return eval($code);
    }

    public function getClass() {
        if (!isset($this))
            trigger_error(__METHOD__ . ' must be called no an instance!');
        return get_class($this);
    }

    public function getClone() {
        return clone $this;
    }
    
    /**
     * Returns the value of the named property of the instance
     * @param string $name Name of the property
     * @return mixed Value for the property, i.e. $this->$name
     */
    public function getVar($name, $default = null) {
        return $this->__get($name) ? : $default;
    }

    /**
     * Returns all public properties of the class
     * @return array
     */
    public function getPublicVars() {
        $state = (array) $this;
        foreach ( $state as $key => $value ):
            if ( false !== strpos($key, '*') )
                unset ($state[$key]);
        endforeach;
        return $state;
    }

    /**
     * Sets the named property to the specified value
     * @param string $name Name of the property
     * @param mixed $value Value for the property
     */
    public function setVar($name, $value) {
        $this->__set($name, $value);
    }

    /**
     * Returns all properties of the class which define its current state
     * @return array
     */
    public function getState() {
        return get_object_vars($this);
    }

    /**
     * Loads class properties from the passed associative array
     */
    public function setState($data) {
        $data = (array) $data;
        foreach ($data as $property => $value):
            $this->$property = $value;
        endforeach;
    }

    /**
     * Prints the Object in a readable format
     * @return string String representation of the object
     */
    public function __toString() {
        return $this->getClass() . ' Object';
    }

}