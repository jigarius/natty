<?php

/**
 * Contains utility functions for the Natty Framework
 * @author JigaR Mehta | Greenpill Productions
 */

/**
 * Merge one or more arrays and their respective sub-arrays
 * @param array $array1
 * @param array $array2
 * @return array Merged array
 * @todo Write better description
 */
function natty_array_merge_nested(array $array1, array $array2) {
    $arrays = func_get_args();
    unset ($arrays[0]);
    foreach ( $arrays as $array2 ):
        foreach ( $array2 as $key => $value ):
            // If the index does not exist in the first array
            if ( !isset ($array1[$key]) ):
                $array1[$key] = $value;
                continue;
            endif;
            // If the index exists in the first array, merge data
            if ( is_numeric($key) ) {
                if ( !in_array($value, $array1) )
                    $array1[] = $value;
            }
            else {
                $array1[$key] = ( is_array($array1[$key]) && is_array($value) )
                    ? natty_array_merge_nested($array1[$key], $value)
                    : $array1[$key] = $value;
            }
        endforeach;
    endforeach;
    return $array1;
}

/**
 * Merges all values of argument 1 which are present in argument 2. Basically,
 * it merges the 2nd array with the intersction of the two arrays.
 * @param array $array1
 * @param array $array2
 * @return array Intersected and merged array.
 */
function natty_array_merge_intersection(array $array1, array $array2) {
    $array2 = array_intersect_key($array2, $array1);
    return array_merge($array1, $array2);
}

function natty_compare_ooa($i1, $i2) {
    
    $i1 = (array) $i1;
    $i2 = (array) $i2;
    
    if ( isset ($i1['_ooa']) && isset ($i2['_ooa']) )
        return $i1['_ooa'] - $i2['_ooa'];
    
    if ( isset ($i1['ooa']) && isset ($i2['ooa']) )
        return $i1['ooa'] - $i2['ooa'];
    
    return 1;
    
}

/**
 * Dumps a one or more variables and stops execution.
 * @param mixed $variable One or more variables to dump.
 * @return void
 */
function natty_debug($variable = NULL) {
    
    // Dump all arguments
    foreach ( func_get_args() as $key => $variable ):
        echo natty_print_r($variable, 1) . '<div></div>';
    endforeach;
    
    // Leave a trace finder for the exit call
    $trace = debug_backtrace();
    $trace = array_shift($trace);
    echo 'Halted at: <strong>'.$trace['file'].':'.$trace['line'].'</strong>';
    
    exit;
    
}

/**
 * Include a file from an isolated scope
 * @param string $file
 * @param array $data [optional] Data to pass to the file
 */
function natty_include_isolated( $file, array $data = array () ) {
    extract($data);
    return include $file;
}

/**
 * Tells whether the given path is a relative path or not
 * @param string $path
 * @return bool True or flase
 */
function natty_is_abspath( $path ) {
    return (bool) preg_match('#^(https?://)|^/|^\.#', $path);
}

/**
 * Converts a string to Camel/Pascal Case.
 * @param string $string The subject
 * @param boolean $cfw [optional] Capitalize first word, i.e. PascalCase.
 * Defaults to FALSE.
 */
function natty_strtocamel( $string, $cfw = FALSE ) {
    $words = preg_split('/[\W]/', $string);
    foreach ( $words as $key => $word ):
        if ( $cfw || $key )
            $words[$key] = ucfirst($word);
    endforeach;
    return implode($words);
}

/**
 * Flattens a multi-dimensional array into a single dimension array.
 * @todo Write a better description.
 * @param array $array
 * @return array Flattened one-dimensional array
 */
function natty_array_flatten(array $array) {
    
    $output = array ();
    
    foreach ( $array as $key => $child ):
        if (is_object($child))
            $child = get_class_vars($child);
        if (is_array($child)) {
            $child = natty_array_flatten($child);
            foreach ($child as $child_prop => $child_value):
                $output[$key . ':' . $child_prop] = $child_value;
            endforeach;
        }
        else {
            $output[$key] = $child;
        }
    endforeach;
    
    return $output;
    
}

/**
 * Takes a array of associative arrays and returns the data as a new 
 * associative array. The keys of the returned array are the values at the $key
 * index are the original array.
 * @param array $data Original array
 * @param string $key The index to use for keying.
 * @return array
 */
function natty_array_reindex(array $data = array (), $key) {
    
    $output = array ();
    foreach ( $data as $record ):
        
        if ( !isset ($record[$key]) )
            throw new \InvalidArgumentException('Argument 2 must specify a key which is contained by all arrays in ');
        
        $output[$record[$key]] = $record;
        
    endforeach;
    return $output;
    
}

/**
 * Returns a random string of the specified length
 * @param int $length Length of the output string
 * @param book $signs Whether or not to use special characters for
 * excess randomization
 * @return string The token string
 */
function natty_rand_string( $length = 32, $signs = FALSE ) {
    
    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVXYZ01234567890';
    
    if ( $signs )
        $pool .= '!@#$%^&*()[]{}_-+|=.,;:~';
    
    $pool = str_split($pool);
    $max_index = sizeof($pool) - 1;
    
    // Genrate random chain of strings
    $result = '';
    
    for ( $i = 0; $i < $length-1; $i ++ ):
        $random_char = rand(0, $max_index);
        $result .= $pool[$random_char];
    endfor;
    
    return $result;
    
}

function natty_uniqid($prefix = NULL, $more_entrophy = FALSE) {
    
    $base = $_SERVER['REMOTE_ADDR'] . ':' . rand(0, 1000);
    if ( $prefix )
        $base .= ':' . $prefix;
    
    $uniqid = uniqid($base, $more_entropy);
    
    return md5($uniqid);
    
}

/**
 * Creates a slug string from the passed text.
 * @param string $text The text to convert
 * @return string Iname string
 */
function natty_slug( $text ) {
    $text = strtolower($text);
    $text = preg_replace( '/([\s]|[^a-z])/', '-', $text );
    return $text;
}

/**
 * Tells whether a given string is a valid iname
 * @param string $iname
 * @return True if valid or false if invalid
 */
function natty_is_iname($string) {
    $string = (string) $string;
    return preg_match(NATTY_REGEX_INAME, $string);
}

/**
 * Prints the argument in a readable format
 * @param mixed $variable The variable to print in a readable format
 * @param bool $return Whether to return the output as string
 * @return string String representation of the argument
 */
function natty_print_r($variable, $return = FALSE) {
    
    switch (gettype($variable)):
        case 'boolean':
            $output = ($variable) ? '(true)' : '(false)';
            break;
        case 'NULL':
            $output = '(null)';
            break;
        case 'string':
            $output = $variable;
            if (0 === strlen($variable))
                $output = '(An Empty String)';
            break;
        case 'object':
            
            // Is it an entity?
            if ( is_a($variable, '\\Natty\\ORM\\EntityObject') ) {
                $object_state = $variable->getState();
            }
            else {
                $object_state = get_object_vars($variable);
            }
            ksort($object_state);
            $object_state = print_r($object_state, TRUE);
            
            $object_class = get_class($variable);
            
            $output = $object_class . '::__construct(' 
                . trim($object_state)
            . ')';
            
            break;
        default:
            $output = print_r($variable, 1);
            break;
    endswitch;
    
    if ( $output )
        $output = '<pre>' . $output . '</pre>';
    
    if ( $return )
        return $output;
    
    echo $output;
    
}

/**
 * V.O.D. stands for "Value or Default". Returns the first argument from
 * amongst which is not empty.
 * @param mixed $var The variable for empty-text
 * @param mixed $default The default value to return;
 * Defaults to '' or blank string
 * @return mixed The argument which is not empty.
 */
function natty_vod($var, $default = '') {
    return is_null($var) || empty ($var) ? $default : $var;
}

/**
 * Returns text translations in a preferred language (if translations exist).
 * @param string $text The text which is to be translated
 * @param array $variables [optional] Name value pairs of variables to replace
 * in the output string. See nstr_replace() for replacement logic.
 * @param array $options [optional] Additional options, which include:<br />
 * language: The language of translation<br />
 * package: The ID of the package requesting which uses this text<br />
 * bundle: The bundle to which the text belongs; This helps load all relevant
 * text at one go and prevents multiple database queries
 * @return string Translated text with variable replacements or the original
 * string if translation is not found.
 */
function natty_text($text, $variables = NULL, array $options = array ()) {
    
    // See if internationalization is enabled
    static $i18n;
    if ( is_null($i18n) )
        $i18n = (bool) \Natty::readSetting('system--i18n');
    
    if ( !$i18n ):
        return $variables
            ? natty_replace($variables, $text) : $text;
    endif;
    
    // Validate arguments
    $lid = isset ($options['language'])
            ? $options['language'] : Natty::getOutputLangId();
    $pid = isset ($options['package'])
            ? $options['package'] : 'mod-system';
    $bundle = isset ($options['bundle'])
            ? $options['bundle'] : 'general';
    $hash = md5($text);
    
    // Create a dictionary for caching already evaluated requests
    static $cache;
    if ( !is_array($cache) )
        $cache = array ();
    if ( !isset ($cache[$lid]) )
        $cache[$lid] = array ();
    if ( !isset ($cache[$lid][$pid]) )
        $cache[$lid][$pid] = array ();
    
    // If the translation has not been loaded into static cache
    if ( !isset ($cache[$lid][$pid][$bundle]) ):
        
        // Attempt to load the relevant text collection
        static $stmt;
        if ( !$stmt ):
            $stmt = \Natty::getDbo()
                ->getQuery('SELECT', '%__system_text t')
                ->addColumns(array ('hash', 'text'))
                ->addComplexCondition(array ('t.lid', '=', ':lid'))
                ->addComplexCondition(array ('t.pid', '=', ':pid'))
                ->addComplexCondition(array ('t.bundle', '=', ':bundle'))
                ->prepare();
        endif;

        // Retrieve the collection
        $stmt->execute(array (
            'lid' => $lid,
            'pid' => $pid,
            'bundle' => $bundle
        ));
        $data = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        $cache[$lid][$pid][$bundle] = $data;
        
    endif;
    
    // If a translation was not retrieved, register the text for translation
    if ( !isset ($cache[$lid][$pid][$bundle][$hash]) || empty ($cache[$lid][$pid][$bundle][$hash]) ):
        
        // If the text is not registered for translation
        if ( !isset ($cache[$lid][$pid][$bundle][$hash]) ):
            
            $dbo = \Natty::getDbo();
            $record = compact('lid', 'pid', 'bundle', 'hash');
            $lid_natty = 'en-US';
        
            // Coder's default language is assumed to be en-US. In that case,
            // just register the translation with the database to serve as default
            if ( $lid_natty == $lid ) {
                $record['text'] = $text;
                $dbo->insert('%__system_text', $record);
                $cache[$lid][$pid][$bundle][$hash] = $text;
            }
            // Return the text in coder's default language (en-US) and register
            // a future translation request record
            else {
                // Register the text for future translation
                $dbo->insert('%__system_text', $record);
                // Return text in coder's language
                $options = array (
                    'package' => $pid,
                    'language' => $lid_natty,
                    'bundle' => $bundle
                );
                $cache[$lid][$pid][$bundle][$hash] = natty_text($text, $variables, $options);
            }
            
        endif;
        
    endif;
    
    // Return text with variable replacement (if any)
    $output = $cache[$lid][$pid][$bundle][$hash];
    return $variables
            ? natty_replace($variables, $output) : $output;
    
}

/**
 * Natty variant of str_replace. Takes an asociative array of data to replace 
 * in $subject. Replacement patterns are as given below:<br />
 * [key]: Replaced with the given value<br />
 * [@key]: Replaced with value wrapped in a span.placeholder<br />
 * [tx] and [/tx]: Replaced with given values as is, but this should only be
 * used for indicating tag, where "x" is the tag number.
 * Example: [t1]Click here[/t1] to go to outer space.
 * @param array $data Associative array of data/tokens to replace.
 * @param string $subject The string on which the replacements will take place.
 * @todo Only support twig placeholder patterns.
 * @return string String with data replaced.
 */
function natty_replace(array $data, $subject) {
    $search = array ();
    $replace = array ();
    foreach ( $data as $name => $value ):
        // Replace [name], [tx] and [/tx] tokens.
        $search[] = '[' . $name . ']';
        $replace[] = $value;
        // Repalace {{ name }} tokens.
        $search[] = '{{ ' . $name . ' }}';
        $replace[] = $value;
        // Replace [@name] tokens
        $search[] = '[@' . $name . ']';
        $replace[] = '<span class="placeholder">' . $value . '</span>';
        // Replace {{ @name }} tokens
        $search[] = '{{ @' . $name . ' }}';
        $replace[] = '<span class="placeholder">' . $value . '</span>';
    endforeach;
    return str_replace($search, $replace, $subject);
}

/**
 * Sorts an array of arrays as per the tree structure they form. This is done
 * based on object id, parent id and level/depth of the item in the tree.
 * @param array $items An array of associative arrays or objects having
 * at least the following indexes/properties: parentId, level
 * @param array $options Options include:<br />
 * idKey: Index indicating ID of the item - defaults to "id"<br />
 * parentKey: Index indicating Parent ID - defaults to "parentId";<br />
 * levelKey: Index indicating the vertical position of the item
 * in the tree - defaults to "level";<br />
 * ooaKey: Index indicating the order of appearance (for sorting). If provided,
 * each branch would be returned sorted by this index.<br />
 * parent: For internal use - ID of the item whose children are to be returned;<br />
 * level: For internal use - the level whose children are to be returned;
 * @return array An array of items organized for tree presentation.
 */
function natty_sort_tree( array $items, array $options = array () ) {
    
    // Merge with default options
    $options = array_merge(array (
        'parentKey' => 'parentId',
        'levelKey' => 'level',
        'idKey' => 'id',
        'ooaKey' => NULL,
        'parent' => 0,
        'level' => NULL,
        'sorted' => FALSE,
    ), $options);
    extract($options);
    
    // Sort input array by parent id and then by order of appearance
    if ( !$options['sorted'] && !is_null($options['ooaKey']) ):
        
        $callback_args = '$item1, $item2';
        $callback_body = '';
        
        // Treat items as arrays
        $callback_body .= ' $item1 = (array) $item1; $item2 = (array) $item2;';
        
        // If Parent IDs are not same, sort by Parent ID
//        $callback_body .= ' $output = $item1["' . $parentKey . '"] - $item2["' . $parentKey . '"];'
//                        . ' if (0 !== $output) return $output;';
        // Sort by OOA
//        $callback_body .= ' natty_debug($item1["' . $ooaKey . '"], $item2["' . $ooaKey . '"]);';
        $callback_body .= ' return intval($item1["' . $ooaKey . '"]) - intval($item2["' . $ooaKey . '"]);';
        $callback_func = create_function($callback_args, $callback_body);
        
        uasort($items, $callback_func);
        $options['sorted'] = 1;
        
    endif;
    
    $output = array ();
    foreach ( $items as $item ):
        
        $record = (array) $item;
        
        if ( !isset ($record[$idKey]) || !isset ($record[$levelKey]) )
            trigger_error('Missing required indices on tree record!', E_USER_ERROR);
        
        if ( $record[$parentKey] != $parent )
            continue;
        if ( !is_null($level) && $record[$levelKey] != $level )
            continue;
        
        // Push the item
        $output[$record[$idKey]] = $item;
        
        // Queue its children right under it
        $children_options = $options;
        $children_options['parent'] = $record[$idKey];
        $children_options['level'] = $record[$levelKey]+1;
        $children = natty_sort_tree($items, $children_options);
        foreach ( $children as $child_key => $child ):
            $output[$child_key] = $child;
        endforeach;
        
    endforeach;
    
    return $output;
    
}
        
/**
 * Returns the value at a given index of an array. Dots in the index
 * are treated as nested arrays. Example: key would look in $array[$key];
 * However, key.subkey will look in $array[key][subkey].
 * @param array $array
 * @param string $key
 * @return mixed The value at the given index or NULL
 */
function natty_array_get(array $array, $key) {
    // Simple value
    if ( FALSE === strpos($key, '.') ) {
        return isset ($array[$key]) ? $array[$key] : NULL;
    }
    // Nested value! Darn!
    else {
        $key_parts = explode('.', $key);
        $value = $array;
        while ( sizeof($key_parts) > 0 ):
            $key = array_shift($key_parts);
            if ( !isset ($value[$key]) )
                return;
            $value = $value[$key];
        endwhile;
        return $value;
    }
}

/**
 * Works like natty_array_get but for setting deep values.
 * @param array $array
 * @param string $key
 * @param mixed $value
 */
function natty_array_set(array &$array, $key, $value) {
    // Simple value
    if ( false === strpos($key, '.') ) {
        $array[$key] = $value;
    }
    // Nested value! Darn again!
    else {
        $key_parts = explode('.', $key);
        $temp =& $array;
        while ( sizeof($key_parts) > 1 ):
            $key = array_shift($key_parts);
            if ( !isset ($temp[$key]) || !is_array($temp[$key]) )
                $temp[$key] = array ();
            $temp =& $temp[$key];
        endwhile;
        // The last sub-part is the value holder!
        $key = array_shift($key_parts);
        $temp[$key] = $value;
    }
}

/**
 * Reads or writes an item in the global static cache.
 * @staticvar array $cache
 * @param string $key The key to read/write.
 * @param mixed $value Fallback value for get requests. For write requests,
 * this is the updated value. To delete a key, pass value as NULL.
 * @param type $write
 * @return mixed Value in the given key.
 */
function &natty_cache($key, $value = NULL, $write = FALSE) {
    
    static $cache;
    if ( !is_array($cache) )
        $cache = array ();
    
    // Read
    if ( !$write ) {
        
        // Create key if not exists
        if ( !array_key_exists($key, $cache) )
            $cache[$key] = $value;
        
        return $cache[$key];
        
    }
    // Write
    else {
        
        if (is_null($value))
            unset ($cache[$key]);
        else
            $cache[$key] = $value;
        
        return $value;
        
    }
    
}

/**
 * Formats the given date/time in the given format.
 * @param mixed $datetime
 * @param array $options
 * @return string Formatted date and time.
 */
function natty_format_datetime($datetime, array $options = array ()) {
    
    // Merge with defaults
    $options = array_merge(array (
        'format' => 'dateonly',
    ), $options);
    
    // Convert datetime to timestamp
    if ( !is_numeric($datetime) )
        $datetime = strtotime($datetime);
    
    $output = '-';
    
    // Render the date
    switch ( $options['format'] ):
        case 'dateonly':
            $format = \Natty::readSetting('system--datetimeDateOnly');
            break;
        case 'timeonly':
            $format = \Natty::readSetting('system--datetimeTimeOnly');
            break;
        case 'datetime':
            $format = \Natty::readSetting('system--datetimeDateTime');
            break;
        case 'relative':
            break;
        default:
            $format = $options['format'];
    endswitch;
    
    return date($format, $datetime);
    
}

/**
 * 
 * @param type $amount
 * @param array $options
 * @return string
 */
function natty_format_money($amount, array $options = array ()) {
    
    // Merge with defaults
    $options = array_merge(array (
        'currency' => \Natty::getCurrencyId(),
        'symbol' => TRUE,
        'code' => FALSE,
        'decimal' => NULL,
    ), $options);
    
    $cid = $options['currency'];
    
    // Load currency data
    static $cache;
    if ( !is_array($cache) )
        $cache = array ();
    if ( !isset ($cache[$cid]) )
        $cache[$cid] = \Natty::getEntity('system--currency', $cid);
    $currency = $cache[$cid];
    
    // Render value
    $output = '';
    if ($currency)
        $output = '<span class="amount">' . number_format($amount, $currency->decimalPlaces, $currency->decimalSymbol, $currency->thouSeparator) . '</span>';
    else
        return '<span class="amount">' . number_format($amount) . '</span>';
    
    // Space between the unit and the amount
    $spacing = $currency->unitSpacing
            ? ' ' : '';
    
    // Render with symbol
    if ( $options['symbol'] ) {
        $symbol_markup = '<span class="symbol">' . $currency->unitSymbol . '</span>';
        $output = $currency->unitFirst
            ? $symbol_markup . $spacing . $output
            : $output . $spacing . $symbol_markup;
    }
    // Render with ISO Code
    elseif ( !$options['code'] ) {
        $output = '<span class="code">' . $currency->unitCode . '</span> ' . $output;
    }

    return $output;
    
}