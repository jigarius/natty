<?php

/**
 * An interface to deal with code libraries
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class Importer {

    /**
     * Whether the importer has been registered
     * @var bool
     */
    private static $registered = false;
    
    /**
     * Lookup direcotries for the Importer
     * @var array
     */
    public static $directories = array ();
    
    /**
     * Includes class files from a folder depending on the namespace
     * @example Importer::import('Natty\\Paginator') loads the file 
     * /library/natty/paginator.php
     * @param string $classname The class name including namespace
     * @throws RuntimeException on failure to find class file in library
     */
    public static function import($classname) {
        
        $classname = strtolower($classname);
        
        // Interpreting Filename
        $path = str_replace(array ('-', '\\'), array ('_', '/'), $classname) . '.php';
        
        foreach ( self::$directories as $key => $dir ):
            $file = $dir . '/' . $path;
            if ( is_file($file) )
                return include_once $file;
        endforeach;
        
        /*
         * If the class was a non-namespaced class, assume a namespace
         * identical to the classname. Example: For the class PHPMailer,
         * lookup for the file phpmailer/phpmailer.php
         */
        if ( FALSE === strpos($classname, '\\') ):
            $classname = $classname . '\\' . $classname;
            return self::import($classname);
        endif;
        
        return FALSE;
        
    }

    /**
     * Registers the Importer as an autoload implementation
     */
    public static function register() {
        if ( !self::$registered )
            spl_autoload_register(array (__CLASS__, 'import'));
    }
    
}