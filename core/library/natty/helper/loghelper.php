<?php

namespace Natty\Helper;

/**
 * Needs revision
 */
natty_debug();

class LogHelper
extends \Natty\StdClass {
    
    /**
     * The context for which the log is being mentained
     * @var string
     */
    protected $context;
    
    /**
     * A handle to the log file
     * @var resource
     */
    protected $handle;
    
    /**
     * 
     * @param string $context The context of the log
     */
    public function __construct( $context ) {
        $this->context = $context ? : 'system/default';
    }
    
    public function __destruct() {
        if ( $this->handle )
            fclose($this->handle);
    }
    
    /**
     * @todo Implement a maximum log file size of 100MB
     */
    protected function getFilename() {
        $filename = \Natty::path('log/' . $this->context . '.log', array ('base' => 'real'));
        return $filename;
    }
    
    protected function open() {
        
        if ( $this->handle )
            return;
            
        $filename = $this->getFilename();

        // Create log directory if not exists
        $dirname = dirname($filename);
        if ( !is_dir($dirname) && !mkdir($dirname, 0755, true) )
            throw new \RuntimeException('Log directory not writable!');

        if ( !$this->handle = fopen($filename, 'a') )
            throw new \RuntimeException('Log directory not writable!');
        
    }
    
    public function write($message, $variables = null) {
        
        $this->open();
        
        // Prepare message
        $message = (string) $message;
        if ( is_array($variables) )
            $message = natty_replace($variables, $message);
        $message = date('Y-m-d H:i:s') . ' ' . $message . "\r\n";
        
        // Write message
        fwrite($this->handle, $message);
        
    }
    
}