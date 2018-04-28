<?php

namespace Natty\Core;

/**
 * Structure of a package installed on the platform.
 * @author JigaR Mehta | Greenpill Productions
 */
class PackageObject
extends \Natty\ORM\EntityObject {

    /**
     * Site-wide unique Package ID
     * @var string
     */
    protected $pid;
    
    /**
     * The internal name for the package
     * @var string
     */
    protected $code;
    
    /**
     * Name of the package
     * @var string
     */
    protected $name;
    
    /**
     * Description of the package
     * @var string
     */
    protected $description;
    
    /**
     * Location of package files within the application
     * @var string
     */
    protected $path;
    
    /**
     * Whether this is a system package
     * @var bool
     */
    protected $isSystem;
    
    /**
     * Type of the package: module or skin
     * @var string
     */
    protected $type;
    
    /**
     * Valid package types
     * @var array
     */
    protected static $types = array ('module', 'skin');
    
    /**
     * Version of the package
     * @var string
     */
    protected $version;
    
    /**
     * The platform version for which the package was written
     * @var string
     */
    protected $platform;

    public function __construct( array $data = array () ) {
        
        // Set data if object is to be constructed from arguments
        $this->setState($data);
        
        // For skins, create an array of positions
        if ( 'skin' == $this->type && !isset ($this->positions) )
            $this->positions = array ();
        
        // Verify package data integrity
        if ( !$this->type || !$this->code )
            throw new \InvalidArgumentException('Required properties "type" and "code" cannot be empty.');
        
        // Check whether the package exists
        if ( !is_dir(NATTY_ROOT . DS. $this->path) )
            throw new \RuntimeException('Files for the package "' . $this->type . '-' . $this->code . '" were not found!');
        
    }
    
    /**
     * @return string Package code
     */
    public function getCode() {
        return $this->code;
    }
    
    /**
     * @return string Returns type of the package
     */
    final public function getType() {
        return $this->type;
    }
    
    /**
     * Returns root-relative path from package-relative path.
     * @param string $path Relative path within the package directory.
     * @param string $prefix [optional] See \Natty::path()
     * @return string Application root relative path.
     */
    public function path($path = NULL, $prefix = NULL) {
        
        if ( $path )
            $path = DS . $path;
        $path = $this->path . $path;
        
        return \Natty::path($path, $prefix);
        
    }
    
    /**
     * Returns stylesheet declarations for the package
     * @return array
     */
    public function getStylesheets() {
        return $this->stylesheets;
    }
    
    /**
     * Returns javascript declarations for the package
     * @return array
     */
    public function getScripts() {
        return $this->scripts;
    }
    
    public function getInstallerClass() {
        $classname = ucfirst($this->type) . '\\' . natty_strtocamel($this->code, TRUE) . '\\Installer';
        if ( !class_exists($classname) )
            $classname = '\\Natty\\Core\\PackageInstaller';
        return $classname;
        
    }
    
    public static function type2typecode($type) {
        switch ( strtolower($type) ):
            case 'module':
            case 'skin':
                return substr($type, 0, 3);
            default:
                return FALSE;
        endswitch;
    }
    
    public static function typecode2type($typecode) {
        switch ( strtolower($typecode) ):
            case 'mod':
                return 'module';
            case 'ski':
                return 'skin';
            default:
                return FALSE;
        endswitch;
    }

    public function setSdata( $value ) {
        if ( is_string($value) )
            $value = natty_vod(unserialize($value), array ());
        $this->sdata = $value;
    }
    
    public function execute($action, $arguments) {
        
        // Execute method can only be called on modules
        if ( 'module' !== $this->type )
            throw new \BadMethodCallException('Only "module" type packages support the ' . __METHOD__ . ' method.');
        
        $_file = $this->path('logic/' . $action . '.php', 'real');
        if ( !is_file($_file) )
            return FALSE;
        
        // Execute the action-specific logic and return status or output
        $output = FALSE;
        include $_file;
        return $output;
        
    }
    
    /**
     * Returns a real path to the override file (if exists) or the original
     * file referenced by the given path.
     * @param string $path Path to the file
     * @return string Path to the given file within the application or the
     * skin-specific override (if exists).
     */
    public function file($path) {
        
        $filename = $this->path($path, 'real');
        
        if ( 'module' === $this->type )
            return $filename;
        
        return is_file($filename)
            ? $filename : \Natty::path('core/' . $path, 'real');
        
    }
    
}