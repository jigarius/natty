<?php

namespace Natty\Core;

class Response
extends \Natty\SingletonObject {

    /**
     * Response attributes
     * @var type 
     */
    protected $attributes = array();
    
    /**
     * Response head data - attachments and meta-data
     * @var array
     */
    protected $head = array ();
    
    /**
     * Links for the breadcrumb trail as an array. Each link may be a string
     * or a renderable array.
     * @var array
     */
    public $breadcrumbs = array ();
    
    /**
     * Flags for the response document. In HTML response, these flags are
     * rendered as classnames to the document body.
     * @var array
     */
    public $flags = array ();
    
    /**
     * Global data to be passed to the response document
     * @var array
     */
    public $variables = array ();
    
    /**
     * Response content as per skin positions
     * @var array
     */
    public $output = array (
        'content' => array ()
    );
    
    /**
     * Adds a head element containing either meta-data or attachments like
     * scripts, stylesheets, etc.
     * @param string $type One of meta|link|stylesheet|script
     * @param array $definition Definition of the elment as a rarray
     */
    public function addHead($type, array $definition) {
        
        // Create a pocket
        if ( !isset ($this->head[$type]) )
            $this->head[$type] = array ();
        
        // Unique element?
        if ( isset ($definition['_key']) ) {
            $this->head[$type][$definition['_key']] = $definition;
        }
        // Non-unique element
        else {
            $this->head[$type][] = $definition;
        }
        
    }
    
    public function addLink(array $definition) {
        $this->addHead('link', $definition);
    }
    
    public function addMeta(array $definition) {
        $this->addHead('meta', $definition);
    }
    
    /**
     * Adds a script reference to the response
     * @param string|array $definition Path to the script or script tag
     * definition as a rarray
     * or attributes for the script tag
     */
    public function addScript($definition) {
        if ( is_string($definition) )
            $definition = array ('src' => $definition);
        $definition = array_merge(array (
            '_type' => 'general',
            'src' => FALSE,
            'type' => 'text/javascript',
        ), $definition);
        $this->addHead('script', $definition);
    }

    /**
     * Adds a cascading stylesheet reference to the response
     * @param string|array $definition Path to the stylesheet or link tag
     * definition as a rarray
     * or attributes for the link tag
     */
    public function addStylesheet($definition) {
        if ( is_string($definition) )
            $definition = array ('href' => $definition);
        $definition = array_merge(array (
            'href' => FALSE,
            'media' => 'all',
            'type' => 'text/css',
            'rel' => 'stylesheet'
        ), $definition);
        $this->addHead('stylesheet', $definition);
    }
    
    /**
     * Saves or returns response attributes
     * @param string $name Name of the attribute
     * @param mixed $value [optional] Value to be stored
     * @return mixed
     */
    public function attribute($name, $value = null) {
        $name = strtolower($name);
        switch (func_num_args()):
            case 1:
                return isset ( $this->attributes[$name] )
                    ? $this->attributes[$name] : NULL;
                return;
            case 2:
                return $this->attributes[$name] = $value;
            default:
                throw new \BadMethodCallException('Expected either one or two arguments.');
        endswitch;
    }

    /**
     * 
     * @param type $string
     * @param type $replace
     * @param type $http_response_code
     */
    public function header($string, $replace = NULL, $http_response_code = NULL) {
        header($string, $replace, $http_response_code);
    }

    public function getState() {
        
        $output = parent::getState();
        
        // This code should be in system--responseRevise
        $output['flags'] = array_unique($output['flags']);
        $output['flags'] = implode(' ', $output['flags']);
        
        return $output;
        
    }
    
    /**
     * Redirects the Response to another URL
     * @param string $location Target location
     * @param int $status_code HTTP Status Code
     */
    public static function redirect($location, $status_code = NULL) {

        if ( empty ($location) )
            throw new \InvalidArgumentException('Argument 1 expected to be a non-empty string!');

        $location = \Natty::url($location);
        
        $status_code = is_int($status_code) ? $status_code : NULL;
        header('Location: ' . $location, true, $status_code);
        exit;
        
    }

    /**
     * Redirects the request to the first one available of the following 
     * locations: REQUEST.bounce, $location, HTTP Referer, Home page
     * @param string $location [optional] The location to bounce back to.
     */
    public static function bounce( $location = NULL ) {
        
        // Detect bounce
        $url = \Natty::getRequest()->getVar('bounce');
        
        // Detect $location
        if ( !$url )
            $url = $location;
        
        // Detect referer
        if ( !$url ):
            $url = isset ($_SERVER['HTTP_REFERER'])
                ? $_SERVER['HTTP_REFERER'] : NULL;
        endif;
        
        // Detect homepage
        if ( !$url )
            $url = NATTY_BASE;
        
        self::redirect($url);
        
    }

    /**
     * Redirects the request to the HTTP Request URI, thereby refreshing the
     * request clearing all POST data
     */
    public static function refresh() {
        self::redirect($_SERVER['REQUEST_URI']);
    }

}