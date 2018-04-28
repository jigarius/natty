<?php

namespace Natty\Helper;

die('Needs improvement');

// Mcrypt library must exist
if ( !function_exists('mcrypt_encrypt') )
    die ('Cannot use EncryptHelper without "mcrypt" libraries.');

abstract class EncryptionHelper
extends \Natty\Uninstantiable {
    
    protected static function touchKey(&$key) {
        
        if ( is_null($key) )
            $key = \Natty::readSetting('system--cipherKey');
        
    }
    
    public static function encrypt($data, $key = NULL) {
        
        self::touchKey($key);
        
        // Must have a key
        if ( !$key )
            throw new \InvalidArgumentException('Argument 2 must be a non-empty string.');
        
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        
        return $output;
        
    }
    
    public static function decrypt($data, $key = NULL) {
        
        self::touchKey($key);
        
        // Must have a key
        if ( !$key )
            throw new \InvalidArgumentException('Argument 2 must be a non-empty string.');
        
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        
        return $output;
        
    }
    
}