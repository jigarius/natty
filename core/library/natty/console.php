<?php

namespace Natty;

abstract class Console {

    /**
     * Message type debug
     * @var string
     */
    const MSG_DEBUG = 'debug';

    /**
     * Message type error
     * @var string
     */
    const MSG_ERROR = 'error';

    /**
     * Message type notice
     * @var string
     */
    const MSG_NOTICE = 'notice';

    /**
     * Message type success
     * @var string
     */
    const MSG_SUCCESS = 'success';

    /**
     * Registers a session-specific message to be delivered to the user
     * @param string $content Message body
     * @param array $options [optional] Additional options, including:<br />
     * type: One of Console::MSG_* constants; Defaults to a NOTICE message;<br />
     * heading: A heading for the message;<br />
     * unique: Whether multiple instances of the same message are to be
     * treated as only one.<br />
     * dismissable: @todo<br />
     * timeout: @todo<br />
     */
    public static function message($content, array $options = array ()) {
        
        $definition = array_merge(array (
            'display' => 1,
            'type' => self::MSG_NOTICE,
        ), $options);
        $definition['content'] = $content;
        
        if (!isset ($_SESSION['system.log']))
            $_SESSION['system.log'] = array();
        
        // Unique message to be displayed only once?
        if ( isset ($definition['unique']) ) {
            $key = md5($definition['content']);
            $_SESSION['system.log'][$key] = $definition;
        }
        else {
            $_SESSION['system.log'][] = $definition;
        }
        
    }

    public static function error($content = NULL, array $options = array ()) {
        
        if ( !$content )
            $content = NATTY_ACTION_FAILED;
        
        $options['type'] = self::MSG_ERROR;
        self::message($content, $options);
        
    }

    public static function success($content = NULL, array $options = array ()) {
        
        if ( !$content )
            $content = NATTY_ACTION_SUCCEEDED;
        
        $options['type'] = self::MSG_SUCCESS;
        self::message($content, $options);
        
    }

    /**
     * Renders the variable as a system message for debugging purposes
     * @param mixed $data
     */
    public static function debug($data) {

        $variables = func_get_args();
        foreach ($variables as $key => $data):

            // Convert the variable to a readable format
            $message = natty_print_r($data, TRUE);
            $heading = null;

            // Show variable number if more than one were passed
            if (sizeof($variables) > 1)
                $heading = 'Variable ' . ($key + 1) . ' ' . $heading;

            // Append caller information
            $trace = debug_backtrace();
            $trace = array_shift($trace);
            $message .= '<div>From: <strong>' . $trace['file'] . ':' . $trace['line'] . '</strong></div>';

            self::message($message, array (
                'heading' => $heading,
                'type' => 'debug',
            ));

        endforeach;
        
    }

    public static function render() {

        // No messages logged
        if (!isset ($_SESSION['system.log']))
            return;

        $output = '<div class="n-console">';
        foreach ($_SESSION['system.log'] as $key => $definition):
            
            switch ( $definition['type'] ):
                case self::MSG_DEBUG:
                    $user = \Natty::getUser();
                    if ( 1 != $user->uid )
                        $definition['display'] = 0;
                    $definition['class'] = 'n-state-default';
                    break;
                case self::MSG_ERROR:
                    $definition['class'] = 'n-state-error';
                    break;
                case self::MSG_SUCCESS:
                    $definition['class'] = 'n-state-ok';
                    break;
                default:
                    $definition['class'] = 'n-state-info';
            endswitch;
            
            if ( !$definition['display'] )
                continue;
            
            $output .= '<div class="n-message ' . $definition['class'] . '">';
            
            // Show heading
            if ( isset ($definition['heading']) )
                $output .= '<strong class="heading">' . $definition['heading'] . '</strong>';
            
            // Multiple messages in content?
            if ( is_array($definition['content']) ):
                
                $message_markup = '<ul class="n-list-bullet">';
                foreach ( $definition['content'] as $message ):
                    $message_markup .= '<li>' . $message . '</li>';
                endforeach;
                $message_markup .= '</ul>';
                
                $definition['content'] = $message_markup;
                unset ($message_markup);
                
            endif;
            
            $output .= '<div class="body">' . $definition['content'] . '</div>';
            $output .= '</div>';
            
            // Clear the message once rendered
            unset ($_SESSION['system.log'][$key]);
            
        endforeach;
        $output .= '</div>';

        return $output;
        
    }

}
