<?php

namespace Module\System;

use Natty\Helper\DatabaseCacheHelper;

class Controller
extends \Natty\Core\PackageObject {
    
    public function executeCron() {
        
        $output = array ();
        try {
            \Natty::trigger('system--cron', $output);
        } 
        catch ( \Exception $e ) {
            $output[] = $e->getMessage();
        }
        
        if ( $output )
            \Natty\Console::debug($output);
        
        \Natty::writeSetting('system--cronExecTime', time());
        return $output;
        
    }
    
    public function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public function onSystemBlockDeclare(&$data) {
        include 'declare/system-block.php';
    }
    
    public function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public function onSystemBoot() {
        
        // Execute cron-job
        $cron_interval = \Natty::readSetting('system--cronInterval');
        
        if ( $cron_interval > 0 ):
            $cron_exectime = \Natty::readSetting('system--cronExecTime', 0);
            $current_time = time();
            if ( ($current_time - $cron_exectime) > $cron_interval * 3600 )
                $messages = $this->executeCron();
        endif;
        
        // See if site is online
        if ( \Natty::readSetting('system--offlineModeEnabled') && 'sign-in' !== \Natty::getCommand() ):
            
            $user = \Natty::getUser();
            if ( !$user->can('system--override offline mode') ):
                $output = natty_render_template(array (
                    '_template' => 'module/system/tmpl/offline-mode',
                ));
                echo $output;
                exit;
            endif;
            
        endif;
        
    }
    
    public function onSystemBeforeRender($data = NULL) {
        
        // Include assets / resources from all packages
        $command = \Natty::getCommand();
        $response = \Natty::getResponse();
        $route = \Natty::getRoute();

        // Prepare title for the response document
        if ( !$response->attribute('title') )
            $response->attribute('title', 'Untitled');

        // Add jQuery libraries
        $response->addScript(array (
            '_type' => 'system',
            'src' => \Natty::path('core/plugin/jquery/jquery.v2.0.min.js')
        ));

        // Add KendoUI libraries
        $response->addStylesheet(array (
            '_type' => 'system',
            'href' => \Natty::path('core/plugin/kendoui/styles/web/kendo.common.core.css'),
        ));
        $response->addStylesheet(array (
            '_type' => 'system',
            'href' => \Natty::path('core/plugin/kendoui/styles/web/kendo.metro.css'),
        ));
        $response->addScript(array (
            '_type' => 'system',
            'src' => \Natty::path('core/plugin/kendoui/js/kendo.ui.core.js'),
        ));
        
        // Add Font Awesome libraries
        $response->addStylesheet(array (
            '_type' => 'system',
            'href' => NATTY_BASE . 'core/plugin/font-awesome/style.css',
        ));
        
        // Add Bootstrap libraries
        $response->addStylesheet(array (
            '_type' => 'system',
            'href' => NATTY_BASE . 'core/plugin/bootstrap/bootstrap.css',
        ));
        
        // Add Natty libraries
        $response->addStylesheet(array (
            '_type' => 'system',
            'href' => \Natty::path('core/plugin/natty/natty.css'),
        ));
        $response->addScript(array (
            '_type' => 'system',
            'src' => \Natty::path('core/plugin/natty/natty.js'),
        ));
        
        // Pass server-side settings
        $js_settings = array (
            'system--routeClean' => \Natty::readSetting('system--routeClean'),
        );
        $response->addScript(array (
            '_data' => 'var Natty = Natty || {};'
                    . ' Natty.base = "' . NATTY_BASE . '";'
                    . ' Natty.command = ' . json_encode($command) . ';'
                    . ' Natty.settings = ' . json_encode($js_settings) . ';'
        ));
        
        // Determine the skin to use
        $skin = \Natty::getSkin();
        $response->variables['skin'] = $skin;

        // A list of all packages affecting the response
        $package_coll = \Natty::getPackage('module');
        $package_coll = array_values($package_coll);
        $package_coll[] = $skin;
        
        // Retrieve embeds from cache
        $cache_key = $skin->code . ':embeds';
        if (TRUE || !$embeds = DatabaseCacheHelper::read('system', $cache_key)):
            
            $stylesheets = array ();
            foreach ( $package_coll as $package ):

                // Register stylesheets
                $package_stylesheets = $package->getStylesheets();
                if ( !$package_stylesheets )
                    continue;

                foreach ( $package_stylesheets as $basename => $attributes ):
                    
                    if ( !is_array($attributes) ):
                        $basename = $attributes;
                        $attributes = array ();
                    endif;
                    
                    $attributes['href'] = $package->path($basename);
                    $stylesheets[] = $attributes;
                    
                endforeach;

            endforeach;
            
            $scripts = array ();
            foreach ($package_coll as $package):

                // Register scripts
                $package_scripts = $package->getScripts();
                if ( !$package_scripts )
                    continue;

                foreach ( $package_scripts as $basename => $attributes ):
                    
                    if ( !is_array($attributes) ):
                        $basename = $attributes;
                        $attributes = array ();
                    endif;
                    
                    $attributes['src'] = $package->path($basename);
                    $scripts[] = $attributes;
                    
                endforeach;

            endforeach;
            
            // Put it in the cache
            $embeds = compact('stylesheets', 'scripts');
            DatabaseCacheHelper::write('system', $cache_key, $embeds);

        endif;
        
        // Get rebuild time
        $rebuild_time = \Natty::readSetting('system--rebuildTime');
        
        // Add stylesheets
        foreach ( $embeds['stylesheets'] as &$t_stylesheet ):
            $t_stylesheet['href'] .= '?sign=' . $rebuild_time;
            $response->addStylesheet($t_stylesheet);
            unset ($t_stylesheet);
        endforeach;
        
        // Add scripts
        foreach ( $embeds['scripts'] as &$t_script ):
            if ( !is_array($t_script) )
                $t_script = array ('src' => $t_script);
            if ( isset ($t_script['src']) )
                $t_script['src'] .= '?sign=' . $rebuild_time;
            $response->addScript($t_script);
            unset ($t_script);
        endforeach;
        
        // Prepare flags
        if ( $route )
            $response->flags[] = 'mod-' . $route->module;
        $response->flags[] = 'url-' . str_replace('/', '-', $command);
        
        // Home flag
        if ($command === \Natty::readSetting('system--routeDefault')) {
            $response->flags[] = 'is-home';
            $response->variables['isHome'] = TRUE;
        }
        else {
            $response->flags[] = 'not-home';
            $response->variables['isHome'] = FALSE;
        }
        
        // Add global template variables
        $response->variables['system_siteName'] = \Natty::readSetting('system--siteName');
        $response->variables['system_siteCaption'] = \Natty::readSetting('system--siteCaption');
        $response->variables['system_siteBase'] = \Natty::readSetting('system--siteBase');
        
        // Site logo image
        $response->variables['system_siteLogo'] = \Natty::readSetting('system--siteLogo');
        if ( $response->variables['system_siteLogo'] )
            $response->variables['system_siteLogo'] = \Natty\Helper\FileHelper::instancePath($response->variables['system_siteLogo'], 'base');
        
        // Add other miscellaneous variables
        $response->variables['command'] = $command;
        
    }
    
    public function onSystemExecute() {
        
        $response = \Natty::getResponse();
        $skin = \Natty::getSkin();
        $binst_handler = \Natty::getHandler('system--blockinst');
        
        // Init all skin positions to avoid E_NOTICE in templates
        foreach ( $skin->positions as $position_code => $position_name ):
            if ( !isset ($response->output[$position_code]) )
                $response->output[$position_code] = array ();
        endforeach;
        
        // Add block data as per block setup
        $binst_coll = $binst_handler->read(array (
            'conditions' => array (
                array ('AND', '1=1'),
            )
        ));
        
        foreach ( $binst_coll as $binst ):
            
            // Determine config for this skin
            if ( !isset ($binst->visibility[$skin->code]) )
                continue;
            $binst_config = $binst->visibility[$skin->code];
            
            // Must specify position
            if ( !$binst_config['position'] )
                continue;
            
            // See if the skin has the given position
            if ( !isset ($skin->positions[$binst_config['position']]) )
                continue;
            
            // Create position pocket
            if ( !isset ($response->output[$binst_config['position']]) )
                $response->output[$binst_config['position']] = array ();
            
            $response->output[$binst_config['position']][] = $binst_handler->getRarray($binst, array (
                'skin_code' => $skin->code,
            ));
            
            unset ($binst);
            
        endforeach;
        
        // Re-arrange output positions as renderables
        foreach ($response->output as $position_code => $block_coll):
            
            // Sort blocks by ooa
            usort($block_coll, 'natty_compare_ooa');
            
            // Prepare individual block rarrays
            foreach ($block_coll as &$block):
                $block['_render'] = 'block';
                unset ($block);
            endforeach;
            
            // Position is a div element containing the blocks
            $position_rarray = array (
                '_render' => 'element',
                '_element' => 'div',
                '_data' => $block_coll,
                'class' => array ('n-position', 'position-' . $position_code),
            );
            
            $response->output[$position_code] = $position_rarray;
            
            // Add flag if position is empty
            if ( 0 === sizeof($block_coll) ):
                $response->flags[] = 'no-' . $position_code;
            endif;
            
            unset ($block_coll);
            
        endforeach;
        
    }
    
    public function onSystemRebuildRegistry() {
        
        $rebuild_start = microtime(TRUE);
        
        // Regenerate CSS & JS Stamp
        \Natty::writeSetting('system--rebuildTime', time());

        // Clear cache
        \Natty\Helper\DatabaseCacheHelper::truncateBin('system');

        // Rebuild entity-type declaration
        \Module\System\Controller::readEntityTypes(TRUE);

        // Rebuild route declaration
        \Module\System\Controller::readRoutes(TRUE);
        
        // Rebuild route declaration
        $email_handler = \Natty::getHandler('system--email');
        $email_handler->rebuild();
        
        $rebuild_time = microtime(TRUE) - $rebuild_start;
        $backtrace = debug_backtrace();
        
        \Natty\Console::message('Registry rebuild called at <strong>' . $backtrace[1]['file'] . ':' . $backtrace[1]['line'] . '</strong> completed in <strong>' . number_format($rebuild_time, 3) . '</strong> seconds.');
        
    }
    
    public static function readBlocks($rebuild = FALSE) {
        
        static $output;
        
        if ( !is_array($output) || $rebuild ):
            
            \Natty::trigger('system--block declare', $output);
            \Natty::trigger('system--block revise', $output);
            
            $block_defaults = array (
                'bid' => NULL,
                'module' => NULL,
                'description' => NULL,
            );
            foreach ( $output as $bid => &$record ):
                
                $bid_parts = explode('--', $bid, 2);
                
                $record['bid'] = $bid;
                $record['module'] = $bid_parts[0];
                $record['helper'] = '\\Module\\' . ucfirst($bid_parts[0]) . '\\Classes\\System\\Block_' . ucfirst($bid_parts[1] . 'Helper');
                
                $record = array_merge($block_defaults, $record);
                
                unset ($record);
                
            endforeach;
            
        endif;
        
        return $output;
        
    }
    
    public static function readEntityTypes($rebuild = FALSE) {
        
        if ( $rebuild )
            return self::rebuildEntityTypes();
        
        $etype_handler = \Natty::getHandler('system--entitytype');
        $existing_etypes = $etype_handler->read(array (
            'condition' => '1=1'
        ));
        return $existing_etypes;
        
    }
    
    public static function rebuildEntityTypes() {
        $output = array ();
        include 'method/rebuild-entitytypes.php';
        return $output;
    }
    
    public static function readRoutes($rebuild = FALSE) {
        
        if ( $rebuild )
            return self::rebuildRoutes();
        
        $route_handler = \Natty::getHandler('system--route');
        $existing_routes = $route_handler->read(array (
            'condition' => '1=1'
        ));
        return $existing_routes;
        
    }
    
    public static function rebuildRoutes() {
        $output = array ();
        include 'method/rebuild-routes.php';
        return $output;
    }
    
    public static function readActions($rebuild = FALSE) {
        
        static $output;
        
        if ( !is_array($output) || $rebuild ):
            
            $output = array ();
            \Natty::trigger('system/actionDeclare', $output);
            \Natty::trigger('system/actionRevise', $output);
            
            $action_defaults = array (
                'module' => NULL,
                'name' => NULL,
                'description' => NULL,
                'isCritical' => FALSE,
            );
            foreach ($output as $aid => &$action):
                $action['aid'] = $aid;
                $aid_parts = explode('--', $aid, 2);
                $action['module'] = $aid_parts[0];
                $action = array_merge($action_defaults, $action);
            endforeach;
            
            uasort($output, function($i1, $i2) {
                
                $output = strcmp($i1['module'], $i2['module']);
                if ( 0 === $output )
                    $output = strcmp($i1['name'], $i2['name']);
                return $output;
                
            });
            
        endif;
        
        return $output;
        
    }

    public static function routePermissionCallback( $action = NULL ) {
        
        $actions = func_get_args();
        $user = \Natty::getUser();
        
        if ( 0 === sizeof($actions) )
            return TRUE;
        
        foreach ( $actions as $action ):
            if ( $user->can($action) )
                return TRUE;
        endforeach;
        
        return FALSE;
        
    }
    
}