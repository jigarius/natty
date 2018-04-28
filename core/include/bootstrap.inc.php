<?php

defined('NATTY') or die;

// Verify root
if ( !defined('NATTY_ROOT') )
    throw new RuntimeException('NATTY_ROOT must be defined before including the Natty bootstrap.');

/**
 * Document root should not have trailing slash
 */
if ( '/' === substr($_SERVER['DOCUMENT_ROOT'], -1) )
    $_SERVER['DOCUMENT_ROOT'] = substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT'])-1);

// Determine base path
$natty_base = dirname($_SERVER['SCRIPT_NAME']);
$natty_base = ( '/' === $natty_base )
        ? '/' : $natty_base . '/';
define('NATTY_BASE', $natty_base);
unset ($natty_base);

/**
 * Natty Version
 */
define('NATTY_VERSION', '1.0');

/**
 * Alias to the DIRECTORY_SEPERATOR constant
 */
define('DS', '/');

// Start session
session_start();

// Standard constants and function definitions
require NATTY_ROOT . '/core/include/constants.inc.php';
require NATTY_ROOT . '/core/include/functions.inc.php';
require NATTY_ROOT . '/core/include/renderers.inc.php';

// Implementing autoload handlers
require NATTY_ROOT . '/core/library/importer.php';
Importer::register();

// Register autoload lookup directories
Importer::$directories[] = NATTY_ROOT . '/core/library';
Importer::$directories[] = NATTY_ROOT . '/core';
Importer::$directories[] = NATTY_ROOT . '/common';

// Imlementing error and exception handlers
//Natty\Helper\DebugHelper::register();