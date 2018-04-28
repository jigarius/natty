<?php

namespace Natty\Helper;

/**
 * File Cache Helper
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class FileCacheHelper {
    
    /**
     * Returns path to a storage directory.
     * @param string $bin Storage bin name - starts with module namespace.
     * @return type
     * @throws \RuntimeException
     */
    protected static function getStorageDirname($bin) {
        
        $dirname = \Natty::readSetting('system--sitePath');
        if ( !$dirname )
            throw new \RuntimeException('Cannot determine site base path');
        
        $bin = str_replace(array ('/', '\\'), '--', $bin);
        
        $output = NATTY_ROOT . '/' . $dirname . '/cache/' . $bin;
        return $output;
        
    }
    
    protected static function getStorageFilename($bin, $key) {
        
        $key = str_replace(array ('/', '\\'), '--', $key);
        
        $dirname = self::getStorageDirname($bin);
        
        return $dirname . '/' . md5($key) . '.cache';
        
    }
    
    public static function write($bin, $key, $data) {
        
        self::createBin($bin);
        
        $filename = self::getStorageFilename($bin, $key);
        
        $data = serialize($data);
        file_put_contents($filename, $data);
        
    }

    public static function read($bin, $key, $ttl = NULL) {
        
        $filename = self::getStorageFilename($bin, $key);
        
        // See if file exists
        if ( !is_file($filename) )
            return NULL;
        
        // See if file is valid
        if ( $ttl ):
            $age = time() - filectime($filename);
            if ( $age > $ttl ):
                unlink($filename);
                return;
            endif;
        endif;
        
        // Return data from cache
        $output = file_get_contents($filename);
        $output = unserialize($output);
        
        return $output;
        
    }

    public static function delete($bin, $key) {
        
        $filename = self::getStorageFilename($bin, $key);
        if ( is_file($filename) ):
            if ( !unlink($filename) )
                throw new \Natty\Core\FilePermException();
        endif;
        
    }
    
    public static function createBin($bin) {
        
        $dirname = self::getStorageDirname($bin);
        
        // Verify existence
        if ( !is_dir($dirname) ):
            if ( !mkdir($dirname, 0755, TRUE) )
                throw new \Natty\Core\FilePermException();
        endif;
        
    }
    
    public static function destroyBin($bin) {
        \Natty\Console::message('Delete bin "' . $bin . '". Code pending.');
    }
    
}