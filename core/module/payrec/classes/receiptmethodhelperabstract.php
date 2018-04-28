<?php

namespace Module\Payrec\Classes;

abstract class ReceiptMethodHelperAbstract
extends \Natty\Uninstantiable {
    
    public static function handleSettingsForm(&$data) {}
    
    public static function attachView($tran, array &$build) {}
    
    public static function doTransaction(&$tran, $method) {
        throw new \BadMethodCallException('The method ' . __METHOD__ . ' must be overridden by child class.');
    }
    
    public static function getDefaultSettings() {
        return array ();
    }
    
}