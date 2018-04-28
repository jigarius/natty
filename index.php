<?php

/**
 * Natty application stamp for preventing direct access to scripts which
 * were not intended to be accessed directly.
 * Verify on first line of all scripts: defined('NATTY') or die;
 */
define('NATTY', TRUE);

/**
 * Installation directory based on index file location - 
 * Current Working Directory
 */
define('NATTY_ROOT', getcwd());

/**
 * Bootstrap the application
 */
require_once NATTY_ROOT . '/core/include/bootstrap.inc.php';

/**
 * See if we're running in installation mode
 */
if ( !Natty::readSetting('system--installed') ):
    if ( !is_dir(NATTY_ROOT . '/install') )
        die ('404: Site Not Found');
    Natty::getResponse()->redirect(NATTY_BASE . 'install/index.php');
    exit;
endif;

define('NATTY_STIME', microtime(TRUE));

// Boot the Application and prepare execution environment
Natty::boot();

// Why doesn't rebuild registry work right after install?
if ( isset ($_REQUEST['rebuild']) && $_REQUEST['rebuild'] && \Natty::getUser()->can('system--administer') ):
    Natty::trigger('system/rebuildRegistry');
    $location = \Natty::getRequest()->getUri();
    $location = str_replace('rebuild=1', 'rebuild=0', $location);
    \Natty::getResponse()->redirect($location);
endif;

// Conduct logic operations
Natty::execute();

// Show time taken
define('NATTY_ETIME', microtime(TRUE));

// Render the response document and terminate
Natty::render();

// Terminate execution without errors
Natty::terminate();