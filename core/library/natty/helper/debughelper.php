<?php

namespace Natty\Helper;

defined('NATTY') or die;

/**
 * Debug Helper
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class DebugHelper
extends \Natty\Uninstantiable {

    /**
     * Whether the Debugger has been registered as error and exception handler
     * @var bool
     */
    private static $registered = FALSE;

    /**
     * Natty Error Handler
     * @param int $type Type of the error
     * @param string $message The error message / descriptino
     * @param string $file The file in which the error ocurred
     * @param int $line The line on which the error ocurred
     * @param mixed $reference The reference object / variable associated
     * with the error
     */
    public static function handleError($type, $message, $file = NULL, $line = NULL, $reference = NULL) {

        switch ($type):
            // Errors which stop script execution
            case E_ERROR:
            case E_USER_ERROR:
                die ($message);
                break;
            // Errors which do not stop script execution
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
                // If the site is in debug mode, display errors
                if (\Natty::readSetting('system--debugMode')):
                    $backtrace = debug_backtrace();
                    \Natty\Console::error($message . '<br />' . self::renderBacktrace($backtrace), array (
                        'heading' => $file . ':' . $line,
                    ));
                endif;
                break;
            case E_WARNING:
            case E_USER_WARNING:
                // If the site is in debug mode, display errors
                if (\Natty::readSetting('system--debugMode')):
                    $backtrace = debug_backtrace();
                    \Natty\Console::error($message . '<br />' . self::renderBacktrace($backtrace), array (
                        'heading' => $file . ':' . $line,
                    ));
                endif;
                break;
        endswitch;
        
    }

    /**
     * Natty Exception Handler
     * @param Exception $e The exception to handle
     */
    public static function handleException($e) {
        if ( !\Natty::readSetting('system--debugMode') )
            die ('An exception has ocurred!');
        echo $e->getMessage() . '<br />' . self::renderBacktrace($e->getTrace());
        exit;
    }
    
    public static function renderBacktrace( $backtrace ) {
        
        // Render debugging data into readable format
        $output = '<ul class="n-backtrace bullet">';
        foreach ( $backtrace as $item ):
            // Render the item
            $output .= '<li>' 
                        . (isset ($item['class']) ? $item['class'] . $item['type'] : '')
                        . (isset ($item['function']) ? $item['function'] : '')
                        . (isset ($item['file']) ? ' in <em>' . $item['file'] . ':' . $item['line'] . '</em>' : '')
                    . '</li>';
        endforeach;
        $output .= '</ul>';
        
        return $output;
        
    }

    public static function register() {
        if ( !self::$registered ):
            set_error_handler(array (__CLASS__, 'handleError'));
            set_exception_handler(array(__CLASS__, 'handleException'));
        endif;
    }
    
}