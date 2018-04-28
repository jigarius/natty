<?php

namespace Natty\Core;

/**
 * Natty Session Handler deals with all Session related common tasks
 * @author JigaR Mehta | Greenpill Productions
 * @package natty
 */
abstract class Session {
    
    /**
     * Destroys the Session cookie and all data
     */
    public static function destroy() {
	return session_destroy();
    }
    
    /**
     * Returns the value of a said value stored in the session, (if set)
     * @param string $property The property to return
     * @return mixed Returns the value of the property or null if not found
     */
    public static function data( $name, $value = null ) {
        switch ( func_num_args() ):
            case 1:
                if ( isset ($_SESSION[$name]) )
                    return $_SESSION[$name];
                break;
            case 2:
                $_SESSION[$name] = $value;
                break;
            default:
                throw new \BadMethodCallException('Must be called with 1 or 2 arguments');
                break;
        endswitch;
    }
    
    /**
     * Returns the ID of the Session
     * @param string $new_id [optional] If provided, the new id would 
     * be set as the Session ID.
     */
    public static function id($new_id = null) {
	return call_user_func_array('session_id', func_get_args());
    }
    
    /**
     * Sets a name for the Session Cookie to be sent to the client's browser.
     * If no arguments are passed, it returns the current name
     * @param string $name The name to set
     * @return void
     */
    public static function name($name = null) {
	return is_null($name)
	    ?session_name():session_name($name);
    }
    
    /**
     * Regenerates the Session ID
     * @param bool $renew Whether to restart the Session
     */
    public static function regenerateId($renew = false) {
	return session_regenerate_id($renew);
    }
    
    /**
     * Stores the said name-value in the active Session
     * @param string $key Key in the format: handler.property
     * @param mixed $value Value to be assigned
     */
    public static function register($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Starts new session or resumes existing session
     */
    public static function start() {
        return session_start();
    }
    
    /**
     * Unsets a given key or all keys for a specified handler
     * @param string $key Name of the key to unset
     * @param string $bin [optional] If set to true, the key is assumed
     * to be a bin name; and all key.* are data is deleted
     */
    public static function unregister($key, $bin = false) {
        
        if ( $bin ) {
            $key .= '.';
            foreach ( $_SESSION as $name => $value ):
                if ( 0 === strpos($name, $key) )
                    unset ($_SESSION[$name]);
            endforeach;
        }
        else {
            unset ( $_SESSION[$key] );
        }
        
    }
    
}