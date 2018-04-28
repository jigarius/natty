<?php

namespace Module\Commerce\Classes;

abstract class CarrierTypeHelperAbstract
extends \Natty\Uninstantiable {
    
    /**
     * Returns an associative array of default settings for this carrier type.
     * @return array
     */
    public static function getDefaultSettings() {
        return array ();
    }
    
    /**
     * Computes a shipping cost based on the given options.
     * @see CarrierHandler::computeCost()
     * @param object $carrier Carrier Entity
     * @param array $options Assoc of options.
     * @return double Shipping cost.
     */
    public static function computeCost($carrier, array $options) {}
    
}