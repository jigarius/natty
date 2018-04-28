<?php

namespace Natty\Helper;

class TwigLoadHelper
implements \Twig_LoaderInterface {
    
    public function getCacheKey($name) {
        return md5($name);
    }
    
    public function getSource($name) {
        
        // Determine the template file
        $skin_root = \Natty::getSkin()->path(NULL, 'real');
        $site_root = \Natty::readSetting('system--siteRoot');
        
        // Lookup file in skin
        $file = $skin_root . DS . $name . '.twig';
        if ( is_file($file) )
            return file_get_contents($file);
        
        // Lookup file in instance
        $file = $site_root . DS . $name . '.twig';
        if (is_file($file))
            return file_get_contents($file);
        
        // Lookup file in common
        $file = NATTY_ROOT . '/common/' . $name . '.twig ';
        if (is_file($file))
            return file_get_contents($file);
        
        // Lookup file in core
        $file = NATTY_ROOT . '/core/' . $name . '.twig ';
        if (is_file($file))
            return file_get_contents($file);
        
        // Lookup failed
        return FALSE;
        
    }
    
    public function isFresh($name, $time) {
        return FALSE;
    }
    
}