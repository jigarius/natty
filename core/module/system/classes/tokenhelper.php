<?php

namespace Module\System\Classes;

class TokenHelper
extends \Natty\Uninstantiable {
    
    public static function readTokenPlaceholders() {
        
        
        
    }
    
    public static function readTokenData($placeholder, $type = NULL, $entity = NULL) {
        
        $pholder_parts = explode(':', $placeholder);
        $type = array_shift($pholder_parts);
        $property = array_shift($pholder_parts);
        
        
        
    }
    
    public static function replaceTokenData($subject, array $data = array ()) {
        
        // Assign global token data
        $data_flat = self::getGlobalTokenData();
        
        // Prepare replacement data
        foreach ( $data as $alias => $value ):
            
            switch ( gettype($value) ):
                case 'object':
                    if ( is_a($value, '\\Natty\\ORM\\EntityObject') ) {

                        /**
                         * @todo Only allowed and well-formatted tokens should be returned.
                         * Sensitive data such as password hash, etc, should be ignored.
                         * Problably this area will trigger an event.
                         * trigger::module--entityTokenData
                         */
                        $data_flat[$alias] = $value->getState();

                    }
                    else {

                        $data_flat[$alias] = $value;
                        
                    }
                    break;
                default:
                    $data_flat[$alias] = $value;
                    break;
            endswitch;
            
        endforeach;
        
        // Flatten the replacement array, replace placeholders and return
        $data_flat = natty_array_flatten($data_flat);
        return natty_replace($data_flat, $subject);
        
    }
    
    protected static function getGlobalTokenData() {
        
        $cache = natty_cache('system--token-global');
        if ( is_null($cache) ):
            
            $cache['system_siteName'] = \Natty::readSetting('system--siteName');
            $cache['system_siteCaption'] = \Natty::readSetting('system--siteCaption');
            
        endif;
        return $cache;
        
    }
    
}