<?php

namespace Natty;

class Uninstantiable
extends StdClass {
    
    final public function __construct() {
        $classname = $this->getClass();
        throw new \RuntimeException('Cannot instantiate object of class ' . $classname);
    }
    
}