<?php

namespace Module\Listing\Classes;

/**
 * Contains methods for handling filter and sorting logics for a given datatype
 */
abstract class DatatypeHelperAbstract 
extends \Natty\Uninstantiable {
    
    /**
     * Data-Type ID
     * @var string
     */
    protected static $dtid = FALSE;
    
    final protected static function getDtid() {
        if ( !static::$dtid )
            trigger_error(__CLASS__ . '::$dtid must be overridden by child class!', E_USER_ERROR);
        return static::$dtid;
    }
    
    /**
     * Returns default settings for the data type
     * @reutrn array An array of default settings for an attribute or instance
     */
    public static function getDefaultSettings($purpose) {
        return array ();
    }
    
    /**
     * Backend filter settings form handler
     * @param array $data
     */
    public static function handleFilterSettingsForm(&$data) {}
    
    /**
     * Backend sort settings form handler
     * @param array $data
     */
    public static function handleSortSettingsForm(&$data) {}
    
}