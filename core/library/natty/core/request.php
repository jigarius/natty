<?php

namespace Natty\Core;

/**
 * Natty HTTP Request
 * @author JigaR Mehta | Greenpill Productions
 */
class Request
extends \Natty\SingletonObject {
    
    public function getCommand() {
        return isset ($_GET['_command'])
            ? $_GET['_command'] : '';
    }
    
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Returns the mentioned variable from the specified Request variable
     * @param string $name Name of the parameter to return
     * @param string $fallback [optional] If the value is not found in the
     * request, this value will be returned instead.
     * @param string $type [optional] The datatype to cast the variable
     * into; One of int,string,bool Defaults to string.
     * @param string $from [optional] The request variable to seek the value
     * in. Defaults to $_REQUEST
     * @return mixed The specified parameter or NULL
     */
    public function getVar($name, $fallback = NULL, $type = NULL, $from = NULL) {

        $type = $type = $type ? : 'string';
        $from = $from ? : 'request';
        $index = '_' . strtoupper($from);
        
        // Verifying if the parameter was set or not
        if (!isset($GLOBALS[$index]) || !isset($GLOBALS[$index][$name]))
            return $fallback;
        $value = $GLOBALS[$index][$name];
        
        // Forcing datatype for the parameter
        settype($value, $type);
        return $value;
        
    }

    public function getBool($name, $fallback = NULL, $from = NULL) {
        return self::getVar($name, $fallback, 'bool', $from);
    }
    
    public function getString($name, $fallback = NULL, $from = NULL) {
        return self::getVar($name, $fallback, 'string', $from);
    }

    public function getInt($name, $fallback = NULL, $from = NULL) {
        return self::getVar($name, $fallback, 'int', $from);
    }
    
    /**
     * Loads an entity of a said type from a request parameter.
     * @param string $name Name of the request parameter
     * @param string $etid Entity Type ID in the form module--model
     * @param string $from [optional] The request variable to use get|post|request
     * @return \Natty\ORM\EntityObject|false
     */
    public function getEntity($name, $etid, $from = NULL) {
        $identifier = $this->getString($name, $from);
        if ( !$identifier )
            return $identifier;
        return \Natty::getEntity($etid, $identifier);
    }
    
    /**
     * Returns the POST array
     * @return array
     */
    public function getPost() {
        return $_POST;
    }

    /**
     * Verifies whether a Request originated from a given URI or Host
     */
    public function isReferredBy() {

        $result = FALSE;

        if (empty ($_SERVER['HTTP_REFERER']))
            return $result;

        natty_debug();
        
    }

}