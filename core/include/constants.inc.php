<?php

defined('NATTY') or die;

/*
 * Other defines and stuff
 */


/**
 * Regex pattern for internal names (iname)
 */
define('NATTY_REGEX_INAME', '/^([a-z|0-9]-?)*[^-]$/');

/**
 * @todo Find a place for this thing!
 */
define('NATTY_ACTION_UNRECOGNIZED', 'Could not recognize the specified action.');
define('NATTY_ACTION_FAILED', 'Action failed.');
define('NATTY_ACTION_FAILED_PARTIALLY', 'Action completed with some errors.');
define('NATTY_ACTION_SUCCEEDED', 'Action completed successfully.');

/**
 * The default skin code
 */
define('NATTY_SKIN_DEFAULT', 'basico');
/**
 * The default language code
 */
define('NATTY_LANG_DEFAULT', 'en-US');

/**
 * Maximum level of nesting allowed
 */
define('NATTY_MAX_LEVELS', 5);