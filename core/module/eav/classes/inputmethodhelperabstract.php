<?php

namespace Module\Eav\Classes;

use Module\Eav\Classes\AttrinstObject;
use Natty\Form\FormObject;

abstract class InputMethodHelperAbstract
extends \Natty\Uninstantiable {
    
    public static function attachValueForm(AttrinstObject $attrinst, FormObject &$form) {}
    
    public static function handleSettingsForm(array &$data) {}
    
    public static function getDefaultSettings() {
        return array ();
    }
    
}