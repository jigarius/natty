<?php

namespace Natty\Core;

abstract class PackageInstaller
extends \Natty\Uninstantiable {
    
    public static function install() {}
    
    public static function uninstall() {}
    
    public static function enable() {}
    
    public static function disable() {}
    
}