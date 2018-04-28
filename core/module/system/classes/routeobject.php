<?php

namespace Module\System\Classes;

class RouteObject
extends \Natty\ORM\EntityObject {
    
    public function execute($purpose = 'content', $command = NULL) {
        
        // Determine command to execute
        if ( is_null($command) )
            $command = \Natty::getCommand();
        
        // Simple permission/content?
        if ('content' === $purpose || 'perm' === $purpose):
            if (!is_null($this->$purpose))
                return $this->$purpose;
        endif;
        
        // Determine callback
        $callback_string = $this->{$purpose . 'Callback'};
        $callback_arguments = $this->{$purpose . 'Arguments'};
        
        // Break up command
        $command_parts = explode('/', $command);
        
        // Extract argument details and load arguments
        foreach ( $this->wildcardType as $wildcard_key => $wildcard_type ):

            // Get the relevant part of the action command
            $wildcard =& $command_parts[$wildcard_key];

            // Validate wildcard type
            switch ( $wildcard_type ):
                // String wildcard
                case 'string':
                    break;
                // Numeric wildcard
                case 'number':
                    if ( !is_numeric($wildcard) )
                        return FALSE;
                    break;
                // Boolean wildcard
                case 'boolean':
                    if ( '1' != $wildcard && '0' != $wildcard )
                        return FALSE;
                    $wildcard = (bool) $wildcard;
                    break;
                // Entity wildcard
                default:
                    
                    // Determine wildcard load callback
                    $wildcard_callback = '\\Natty::getEntity';
                    if ( isset ($this->wildcardCallback[$wildcard_key]) )
                        $wildcard_callback = $this->wildcardCallback[$wildcard_key];
                    
                    // Determine wildcard load arguments
                    $wildcard_arguments = array ($wildcard_type, $wildcard);
                    if ( isset ($this->wildcardArguments[$wildcard_key]) ):
                        $wildcard_arguments = $this->wildcardArguments;
                        foreach ( $wildcard_arguments as $wildcard_argument_key => $wildcard_argument ):
                            if ( is_int($command_parts[$wildcard_argument_key]) )
                                $wildcard_arguments[$wildcard_argument_key] = $command_parts[$wildcard_argument_key];
                        endforeach;
                        unset ($wildcard_argument_key, $wildcard_argument);
                    endif;
                    
                    $wildcard = call_user_func_array($wildcard_callback, $wildcard_arguments);
                    
                    // If wildcard was not loaded, the task failed
                    if ( !$wildcard )
                        \Natty::error(404);
                    
                    break;
            endswitch;
            
            unset ($wildcard_type, $wildcard);

        endforeach;
        
        // Replace wildcardType in arguments
        $arguments = array ();
        foreach ($callback_arguments as $key):
            if ( is_int($key) ) {
                if ( isset ($command_parts[$key]) )
                    $arguments[] = $command_parts[$key];
            }
            else {
                $arguments[] = $key;
            }
        endforeach;
        
        // Execute the callback
        $callback_parts = explode('::', $callback_string);
        switch (sizeof($callback_parts)):
            // Only a module was specified
            /**
             * @todo This should point to module::DefaultController::pageDefault()
             */
            case 1:
                $callback_parts[] = 'pageDefault';
                break;
            // module::controller specified
            case 2:
                break;
            // module::controller::method specified
            case 3:
                $callback_parts = array (
                    '\\Module\\' . ucfirst($callback_parts[0]) . '\\Logic\\' . str_replace('.', '\\', $callback_parts[1]),
                    $callback_parts[2],
                );
                break;
            default:
                return FALSE;
        endswitch;
        
        list ($callback_classname, $callback_method) = $callback_parts;
        
        // If callback classname is just a module code, then use the module
        // package object as the callback object.
        $callback_module = \Natty::getPackage('module', $callback_classname);
        if ($callback_module)
            $callback_classname = $callback_module;
        
        // If callback method does not exist, throw error
        if ( !method_exists($callback_classname, $callback_method) )
            \Natty::error(500, 'Callback ' . $callback_string . ' does not exist.');
        
        return call_user_func_array(array ($callback_classname, $callback_method), $arguments);
        
    }
    
}