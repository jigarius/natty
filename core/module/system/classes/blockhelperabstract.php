<?php

namespace Module\System\Classes;

class BlockHelperAbstract
extends \Natty\Uninstantiable {
    
    public static function handleSettingsForm(&$data) {}
    
    public static function buildOutput(array $settings) {
        return array (
            '_data' => NULL,
        );
    }
    
    public static function onBeforeDelete(&$entity, array $options = array ()) {}
    
    public static function getDefaultSettings() {
        return array ();
    }
    
}