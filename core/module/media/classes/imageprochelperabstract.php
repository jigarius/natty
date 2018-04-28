<?php

namespace Module\Media\Classes;

/**
 * Image Process Helper Abstract
 */
abstract class ImageProcHelperAbstract
extends \Natty\Uninstantiable {
    
    public static function applyToImage(&$image, array $options = array ()) {}
    
    public static function getDefaultOptions() {}
    
    public static function handleSettingsForm(&$data) {}
    
}
