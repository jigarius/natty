<?php

/**
 * Natty application stamp for preventing direct access to scripts which
 * were not intended to be accessed directly.
 * Verify on first line of all scripts: defined('NATTY') or die;
 */
define('NATTY', '0.75');

/**
 * Installation directory based on index file location - 
 * Current Working Directory
 */
define('NATTY_ROOT', dirname(getcwd()));

/**
 * Bootstrap the application
 */
require_once NATTY_ROOT . '/core/include/bootstrap.inc.php';

// Load libraries
use Natty\Core\InstallHelper;

// Determine frontpage URL
$frontend_path = dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/';

// See if an installation is really required
if ( Natty::readSetting('system--installed') ):
    \Natty\Console::message('Natty is already installed.');
    Natty::getResponse()->redirect($frontend_path);
endif;

// If install form is submitted, gather data from there.
if ( isset ($_POST['submit']) ):
    
    // Set site specific settings (from user form)
    $settings_data = array (
        'system--database' => array (
            'default' => $_POST['database'],
        ),
        'system--cipherKey' => md5(uniqid(NULL, TRUE)),
        'system--debugMode' => 1,
    );
    foreach ( $settings_data as $setting_name => $setting_value ):
        Natty::writeSetting($setting_name, $setting_value);
    endforeach;
    
endif;

// If a database has been specified, install Natty
if ( Natty::readSetting('system--database') ):
    
    // Write to the settings file if it does not exist
    $settings_filename = NATTY_ROOT . '/' . Natty::readSetting('system--sitePath') . '/settings.php';
    \Natty::writeSettings($settings_filename);
    
    // Create storage directory
    $files_dirname = NATTY_ROOT . '/' . Natty::readSetting('system--sitePath') . '/files';
    if ( !is_dir($files_dirname) && !mkdir($files_dirname) )
        throw new \RuntimeException('File system permission error.');
    
    // Prevent execution inside the storage directory
    Natty\Helper\FileHelper::protectDir($files_dirname);
    
    // Create cache directory
    $cache_dirname = NATTY_ROOT . '/' . Natty::readSetting('system--sitePath') . '/cache';
    if ( !is_dir($cache_dirname) && !mkdir($cache_dirname) )
        throw new \RuntimeException('File system permission error.');
    
    // Prevent execution inside the storage directory
    Natty\Helper\FileHelper::protectDir($cache_dirname);
    
    // Install all required modules
    $packages = array ('module-system', 'module-people', 'module-taxonomy', 'skin-basico');
    try {
        foreach ( $packages as $pid ):
            list ($type, $code) = explode('-', $pid, 2);
            InstallHelper::install($type, $code);
        endforeach;
    }
    catch ( Exception $e ) {
        natty_debug($e->getMessage());
    }
    
    // Mark Natty as installed
    \Natty::writeSetting('system--installed', 1);
    \Natty::writeSettings($settings_filename);
    
    // Go to front page
    \Natty\Console::success('Natty has been installed.');
    \Natty::getResponse()->redirect($frontend_path);

endif;

/**
 * Display installation form
 * @todo This should be moved to another file. Or better yet, this should be
 * produced by system/logic/install using the regular dispatcher using an
 * alternative Offline RouteHelper
 */
?>
<html>
    <head>
        <title>Install Natty</title>
    </head>
    <body>
        <form method="post" action="">
            <fieldset>
                <legend>Database Server</legend>
                <div class="form-item">
                    <label>Driver</label>
                    <input type="text" name="database[driver]" required="1" />
                </div>
                <div class="form-item">
                    <label>Host</label>
                    <input type="text" name="database[host]" required="1" />
                </div>
                <div class="form-item">
                    <label>Database</label>
                    <input type="text" name="database[dbname]" required="1" />
                </div>
                <div class="form-item">
                    <label>Username</label>
                    <input type="text" name="database[username]" required="1" />
                </div>
                <div class="form-item">
                    <label>Password</label>
                    <input type="text" name="database[password]" />
                </div>
                <div class="form-item">
                    <label>Table Prefix</label>
                    <input type="text" name="database[prefix]" />
                </div>
            </fieldset>
            <fieldset>
                <input type="submit" name="submit" value="Install"/>
            </fieldset>
        </form>
    </body>
</html>