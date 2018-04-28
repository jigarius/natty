<?php

/**
 * Validates the given value using the given callbacks and options.
 * @param mixed $value
 * @param array $validators An array of rules.
 * @param array $arguments [optional] Additional arguments to pass to the callback
 * @return boolean|array A boolean TRUE if the value passes the test otherwise,
 * an array of error messages
 */
function natty_validate( $value, array $validators, array $arguments = array () ) {
    
    $errors = array ();
    
    foreach ( $validators as $entry ):
        
        // Validator format array ($callback, $options)
        $callback = $entry[0];
        $entry[0] = $value;
        
        // Validate the output
        $output = call_user_func_array($callback, $entry);
        if ( TRUE === $output )
            continue;
        
        // Register error messages
        if ( FALSE === $output )
            $output = 'There are some errors with the value you entered.';
        if ( is_string($output) )
            $output = array ($output);
        $errors = array_merge($errors, $output);
        
    endforeach;
    
    return 0 == sizeof($errors) ? TRUE : $errors;
    
}

/**
 * Validates a given variable as an email address.
 * @param string $value
 * @return true|string Error string on failure, otherwise, true.
 */
function natty_validate_email( $value, array $options = array () ) {
    if ( !filter_var($value, FILTER_VALIDATE_EMAIL) )
        return 'Value must be a valid email address like user@domain.com.';
    return true;
}

/**
 * Passes the value through number-specific validations.
 * @param string|int $value The value to test
 * @param array $options [optional] An array containing on of the following
 * additional validation options:<br />
 * <b>minValue:</b> Minimum value check;<br />
 * <b>maxValue:</b> Maximum value check;<br />
 * <b>reqValue:</b> Match against a particular value;<br />
 * <b>language:</b> Language in which the error message should be returned.
 * @todo Respect the language parameter
 * @return true|string Error string on failure, otherwise, true.
 */
function natty_validate_number( $value, array $options = array () ) {
    
    // Check if it is a number
    if ( !is_numeric($value) )
        return 'Value must be a valid number.';
    
    // Check minimum value
    if ( isset ($options['minValue']) && $value < $options['minValue'] )
        return 'Value must be greater than ' . $options['minValue'] . '.';
    
    // Check maximum value
    if ( isset ($options['maxValue']) && $value > $options['maxValue'] )
        return 'Value must be less than ' . $options['maxValue'] . '.';
    
    // Check required value
    if ( isset ($options['reqValue']) && $value != $options['reqValue'] )
        return 'Value must be exactly ' . $options['reqValue'] . '.';
    
    return true;
    
}

/**
 * Passes the value through string-specific validations.
 * @param string $value The value to test.
 * @param array $options [optional] An array containing on of the following
 * additional validation options:<br />
 * <b>minLength:</b> Minimum length check;<br />
 * <b>maxLength:</b> Maximum length check;<br />
 * <b>reqLength:</b> Match against a particular length;<br />
 * <b>match:</b> Match against a regular expression. Should contain an
 * associative array with indices "pattern" & "message";<br />
 * <b>message:</b> If value does not match the "pattern", this is the
 * message which would be returned.<br />
 * <b>language:</b> Language in which the error message should be returned.
 * @todo Respect the language parameter
 * @return true|string Error string on failure, otherwise, true.
 */
function natty_validate_string( $value, array $options = array () ) {
    
    $length = strlen($value);
    
    // Regex pattern validation
    if ( isset ($options['match']) ):
        if ( !preg_match($options['match']['pattern'], $value) ):
            $output = isset ($options['match']['message'])
                ? $options['match']['message'] : 'Value must match the specified pattern.';
            return $output;
        endif;
    endif;
    
    // Minimum length check
    if ( isset ($options['minLength']) && $length < $options['minLength'] )
        return 'Value must be at least ' . $options['minLength'] . ' characters long.';
    
    // Maximum length check
    if ( isset ($options['maxLength']) && $length > $options['maxLength'] )
        return 'Value must be at most ' . $options['maxLength'] . ' characters long.';
    
    // Required length check
    if ( isset ($options['reqLength']) && $length < $options['reqLength'] )
        return 'Value must be exactly ' . $options['reqLength'] . ' characters long.';
    
    return TRUE;
    
}