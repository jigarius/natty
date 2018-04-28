<?php

/**
 * The Natty Orchestrator - In short, meet Natty!
 */
abstract class Natty {

    /**
     * Singleton instances of system objects
     * @var array
     */
    private static $instances = array();
    
    /**
     * A static cache of all settings for this instance
     * @var array
     */
    private static $settings;
    
    /**
     * Creates an object of type $object with $arguments (if any)
     * @param string $object Object code
     * @param array $arguments (optional) Constructor arguments
     * @return object New instance of the object
     * @todo Remove this method if not required
     */
    public static function create($object, array $arguments = array()) {

        // Determine object classname
        $classname = '\\' . str_replace('.', '\\', $object);

        // See if the class exists
        if (!class_exists($classname))
            throw new RuntimeException('Definition could not be found for ' . $classname);

        $reflection = new ReflectionClass($classname);
        $output = $reflection->newInstanceArgs($arguments);

        return $output;
    }

    /**
     * Returns GET._command
     * @return string
     */
    public static function getCommand() {
        static $command;
        if ( is_null($command) ):
            $command = self::readSetting('system--routeDefault', 'sign-in');
            if ( isset ($_GET['_command']) && strlen($_GET['_command']) > 0 ):
                
                $command = $_GET['_command'];
                
                // Convert custom path to system path
                if ( self::readSetting('system--routeRewrite') ):
                    
                    $rewrite_record = self::getDbo()
                        ->read('%__system_rewrite', array (
                            'columns' => array ('systemPath'),
                            'key' => array (
                                'customPath' => $command,
                                'ail' => self::getOutputLangId(),
                            ),
                            'unique' => 1,
                        ));
                    
                    if ( $rewrite_record )
                        $command = $rewrite_record['systemPath'];
                    
                endif;
                
            endif;
        endif;
        return $command;
    }
    
    /**
     * Returns a connection to a given database server.
     * @param string $id [optional] Unique identifier for the connection;
     * Defaults to database.default as set in configuration file
     * @return \Natty\DBAL\Base\Connection
     */
    public static function &getDbo($id = NULL) {

        // Fix arguments
        $id = $id ? : 'default';

        // Create a pocket for database instances
        if (!isset(self::$instances['dbo']))
            self::$instances['dbo'] = array();

        // Return existing instance
        if (!isset(self::$instances['dbo'][$id])):

            // Retrieve database configurations
            $databases = self::readSetting('system--database');
            if (!isset($databases[$id]))
                throw new \RuntimeException('Database "' . $id . '" is not configured correctly!');

            // Extract config for relevant database
            $config = $databases[$id];
            if (empty($config['driver']))
                throw new RuntimeException('Database type not specified for database "' . $id . '"');

            // Register the connection object
            try {
                $connection_class = 'Natty.DBAL.' . ucfirst($config['driver']) . '.Connection';
                $connection = \Natty::create($connection_class, array($config, $id));
            } catch (\PDOException $e) {
                throw new \PDOException('Database connection to ' . $config['host'] . ' failed!');
            }

            // Register the global connection instance
            self::$instances['dbo'][$id] = & $connection;

        endif;

        return self::$instances['dbo'][$id];
        
    }

    /**
     * Returns a date object at the site's timezone
     * @return DateTime
     */
    public static function getDateTime() {
        if (!isset(self::$instances['datetime'])):
            self::$instances['datetime'] = new DateTime(null);
        endif;
        return self::$instances['datetime'];
    }

    public static function getRoute() {
        if ( !isset(self::$instances['route']) ):
            $command = self::getCommand();
            self::$instances['route'] = self::command2route($command);
        endif;
        return self::$instances['route'];
    }

    /**
     * Returns an EntityHandler for the given entity from the given module
     * @param string $type The handler to return in the format: module/model
     * @return \Natty\ORM\EntityHandler An Entity Handler instance
     */
    public static function &getHandler($type) {
        return \Natty\ORM\EntityHandler::getInstance($type);
    }

    /**
     * Returns an entity of the given type with the given ID
     * @param string $type Entity type in the format module/model
     * @param mixed $id Unique identifier for the entity
     * @param array $options [optional] Options for EntityHandler::read().
     * @return \Natty\ORM\EntityObject|false
     */
    public static function getEntity($type, $id, array $options = array ()) {
        return self::getHandler($type)->readById($id, $options);
    }

    /**
     * Returns a Currency ID for the user.
     * @return string
     */
    public static function getCurrencyId() {
        static $cid;
        if ( !$cid ):
            $cid = self::getUser()->idCurrency
                ? : self::readSetting('system--currency', FALSE);
        endif;
        return $cid;
    }

    /**
     * Returns the Language ID in which the user is entering data. It returns
     * REQUEST.ilid and if it is not set, the output language ID.
     * @return string
     */
    public static function getInputLangId() {
        return isset ($_REQUEST['ilid'])
            ? $_REQUEST['ilid'] : self::getOutputLangId();
    }
    
    /**
     * Returns the language ID in which the response is to be rendered.
     * @return string
     */
    public static function getOutputLangId() {
        static $lid;
        if ( !$lid ):
            $lid = self::getUser()->idLanguage
                ? : self::readSetting('system--language', NATTY_LANG_DEFAULT);
        endif;
        return $lid;
    }

    /**
     * Returns all packages or a specific enabled package of a given type.
     * @param string $type Type of package required, module or skin.
     * @param string $code [optional] If a particular package is required,
     * then the code of that package. Example: getPackage("module", "system");
     * @param boolean $rebuild Whether packages should be re-read from the
     * database instead of cache.
     * @return array|Natty\Core\PackageObject An array of objects or a specific
     * package object.
     */
    public static function &getPackage($type, $code = NULL, $rebuild = FALSE) {
        
        // Get items from static cache
        $cache = natty_cache(__METHOD__);
        
        if (!is_array($cache) || $rebuild):
            
            $package_handler = self::getHandler('system--package');
            $package_coll = $package_handler->read(array(
                'key' => array (
                    'status' => 1
                ),
                'ordering' => array(
                    'package.ooa' => 'asc'
                ),
            ));
            
            $cache = array (
                'skin' => array (),
                'module' => array (),
            );
            foreach ($package_coll as $package):
                $cache[$package->type][$package->code] = $package;
            endforeach;
            
            // Put items in static cache
            natty_cache(__METHOD__, $cache, TRUE);
            
        endif;
        
        // Return a particular package
        if (!is_null($code)):
            if (isset ($cache[$type][$code]))
                return $cache[$type][$code];
            $output = FALSE;
            return $output;
        endif;
        
        // Return all packages of a particular type
        return $cache[$type];
        
    }

    /**
     * It returns the skin to be applied to the response depending on whether 
     * it is a front-end request or a back-end request.
     * @param string $code [optional] Code for the string to return; Returns
     * the default site theme by default
     * @return \Natty\Core\PackageObject
     */
    public static function &getSkin($code = NULL) {

        if (!isset(self::$instances['skins']))
            self::$instances = array();

        // If no code is specified, return default skin
        if ( empty ($code) ):
            $route = self::getRoute();
            if ( !$route || !$route->isBackend || !self::getUser()->can('system--view backend skin') ) {
                $code = self::readSetting('system--frontendSkin');
            }
            else {
                $code = self::readSetting('system--backendSkin');
            }
            if ( !$code )
                $code = NATTY_SKIN_DEFAULT;
        endif;

        if (!isset(self::$instances['skins'][$code])):

            // Retrieve skin data
            $handler = self::getHandler('system--package');
            $skin = $handler->readById('ski-' . $code);

            self::$instances['skins'][$code] = $skin;

        endif;

        return self::$instances['skins'][$code];
        
    }

    /**
     * Returns the global Twig Environment.
     * @return \Twig_Environment
     */
    public static function &getTwig() {
        
        static $twig;
        if (is_null($twig)):

            // Register Twig Autoloader
            require NATTY_ROOT . '/core/library/twig/Autoloader.php';
            Twig_Autoloader::register();

            // Prepare the Natty template loader for Twig
            $loader = new \Natty\Helper\TwigLoadHelper();
            $twig = new Twig_Environment($loader, array (
                'cache' => \Natty::readSetting('system--siteRoot') . '/cache/twig',
                'debug' => \Natty::readSetting('system--debugMode', FALSE),
            ));

            // Register the Twig Debug Extension
            if ($twig->isDebug())
                $twig->addExtension(new Twig_Extension_Debug());

        endif;
        
        return $twig;
        
    }
    
    /**
     * Returns the singleton Request Object
     * @return Natty\Core\Request
     */
    public static function &getRequest() {
        return \Natty\Core\Request::getInstance();
    }

    /**
     * Returns the singleton Response Object
     * @return Natty\Core\Response
     */
    public static function &getResponse() {
        return \Natty\Core\Response::getInstance();
    }

    /**
     * Returns the singleton Server Object
     * @return Natty\Core\Server
     */
    public static function &getServer() {
        return \Natty\Core\Server::getInstance();
    }

    /**
     * Returns an entity representing the active user. If no user is signed in,
     * it returns an anonymous user object.
     * @param boolean $redirect [optional] If set to TRUE and a user is not
     * currently logged in, the application would redirect to the sign in page.
     * Defaults to FALSE.
     * @return \Module\People\Classes\UserObject The authenticated user
     */
    public static function &getUser($redirect = FALSE) {
        if (!isset(self::$instances['user'])):
            $handler = self::getHandler('people--user');
            $uid = $handler::getAuthUserId();
            if ($uid) {
                $user = $handler->readById($uid);
            } else {
                if ($redirect)
                    self::getResponse()->redirect('sign-in');
                $user = $handler->create(array(
                    'uid' => 0,
                    'name' => 'Anonymous',
                ));
            }
            self::$instances['user'] = & $user;
        endif;
        return self::$instances['user'];
    }
    
    /**
     * Generates path to a given file or location within the application.
     * @param string $path Root relative path (without initial slash).
     * @param array $prefix The base path to prefix. This would be one of these
     * three options (defaults to "base"):<br />
     * root: Prefixes NATTY_ROOT to give you a disk path.<br />
     * base: Prefixes NATTY_BASE to give you a base relative URL.<br />
     * absolute: Prefixes http://site.com/NATTY_BASE/ to give you an absolute
     * URL to the given resource.
     * @return string Path with the given prefix.
     */
    public static function path($path, $prefix = NULL) {

        // Path cannot be empty
        if ( 0 === strlen($path) )
            throw new InvalidArgumentException('Argument 1 expected to be non-empty string!');
        
        switch ( $prefix ):
            case 'absolute':
                $base = 'http://' . $_SERVER['SERVER_NAME'] . NATTY_BASE;
                break;
            case 'root':
            case 'real':
                $base = NATTY_ROOT . '/';
                break;
            case 'base':
            default:
                $base = NATTY_BASE;
        endswitch;

        return $base . $path;

    }
    
    /**
     * Returns a relative path to the package from NATTY_ROOT.
     * @param string $type
     * @param string $code
     * @return string Path to package.
     */
    public static function packagePath($type, $code) {
        $cache = natty_cache(__METHOD__, array ());
        $pid = substr($type, 0, 3) . '-' . $code;
        if ( !isset ($cache[$pid]) ):
            $package = self::getHandler('system--package')->readById($pid);
            $cache[$pid] = $package->path;
        endif;
        return $cache[$pid];
    }
    
    /**
     * Returns the path to a given location within the current site instance.
     * @param string $path Path name containing "instance://" prefix. This
     * prefix (if found) will be replaced with "instance/site.com".
     * @param string $base Works like the second argument for \Natty::path().
     * @return string Path to the file within the site's directory.
     */
    public static function instancePath($path, $base = 'root') {
        $site_path = 'instance/' . \Natty::readSetting('system--siteSlug') . '/';
        $site_path = self::path($site_path, $base);
        return str_replace('instance://', $site_path, $path);
    }
    
    /**
     * Generates a URL within Natty.
     * @param string $command The system path which the URL would lead to.
     * @param array $data [optional] Associative array of data for query string.
     * @param array $options [optional] Associative array of options:<br />
     * base: The base URL to use. One of absolute|relative or a custom base path.
     * @return string Rendered URL.
     */
    public static function url($command = NULL, array $data = array (), array $options = array ()) {
        
        // $command is already a base-relative or absolute URL, then we have
        // no need for doing anything.
        $command = (string) $command;
        if ( natty_is_abspath($command) )
            return $command;
        
        $route = self::getRoute();
        
        // Fallback to default options
        $options = array_merge(array (
            'base' => 'absolute',
            'protocol' => 'http',
            'fragment' => FALSE,
            'rewrite' => 1 || ($route && !$route->isBackend),
            'ail' => self::getOutputLangId(),
        ), $options);
        
        // Re-writes enabled? Then translate command into user-defined path
        // However, for backend requests, rewrites are disabled by default to
        // avoid unnecessary database queries.
        if ( self::readSetting('system--routeRewrite') && $options['rewrite'] ):
            
            static $cache = array (), $cp_stmt;
            $cache_key = $options['ail'] . ':' . $command;
            
            if ( !isset ($cache[$cache_key]) ):
                
                if ( !isset ($cp_stmt) ):
                    $cp_stmt = self::getDbo()->getQuery('select', '%__system_rewrite r')
                        ->addColumn('customPath', 'r')
                        ->addComplexCondition('AND', '{r}.{systemPath} = :command')
                        ->limit(1)
                        ->prepare();
                endif;
                
                $custom_path = $cp_stmt->execute(array (
                    'command' => $command,
                ))->fetch(\PDO::FETCH_COLUMN);
                
                $cache[$cache_key] = $custom_path;
                
            endif;
            
            // Replace command with custom path
            if ( $cache[$cache_key] )
                $command = $cache[$cache_key];
        
        endif;
        
        // Determine the base path to be used
        switch ( $options['base'] ):
            case 'absolute':
                $base = $options['protocol'] . '://' . $_SERVER['SERVER_NAME'] . NATTY_BASE;
                break;
            case 'relative':
                $base = NATTY_BASE;
                break;
            default:
                $base = $options['base'];
                break;
        endswitch;
        
        // Do we have a bounce parameter
        if ( isset ($data['bounce']) && TRUE === $data['bounce'] ):
            $bounce_url = natty_cache('bounce');
            if ( is_null($bounce_url) ):
                $bounce_command = \Natty::getCommand();
                $bounce_data = $_GET;
                unset ($bounce_data['_command']);
                $bounce_url = \Natty::url($bounce_command, $bounce_data);
                natty_cache('bounce', $bounce_url, TRUE);
                unset ($bounce_command, $bounce_data);
            endif;
            $data['bounce'] = $bounce_url;
        endif;
        
        // Generate clean URL?
        if ( \Natty::readSetting('system--routeClean') ) {
            $output = $base . $command;
            if ( sizeof($data) > 0 )
                $output .= '?' . http_build_query($data);
        }
        // Generate ugly URL!
        else {
            $output = $base . '?_command=' . $command;
            if ( sizeof($data) > 0 )
                $output .= '&' . http_build_query($data);
        }
        
        // Append hash / URL Fragment
        if ( $options['fragment'] )
            $output .= '#' . $options['fragment'];
        
        return $output;
        
    }
    
    public static function command2route($command) {
        
        static $cache;
        if ( !is_array($cache) )
            $cache = array ();
        
        // If not cached
        if ( !isset ($cache[$command]) ):
            
            // Retrieve route handler data
            $route_handler = \Natty::getHandler('system--route');
            $command_parts = explode('/', $command);
            
            $stmt = $route_handler->getQuery()
                    ->addComplexCondition(array (':command', 'LIKE', 'route.rid'))
                    ->addComplexCondition(array ('route.size', '<=', ':size'))
                    ->orderBy('route.size', 'desc')
                    ->orderBy('route.variables', 'asc')
                    ->limit(1);
            $route = $route_handler->execute($stmt, array (
                'parameters' => array (
                    'command' => $command,
                    'size' => sizeof($command_parts),
                ),
            ));
            
            // Prepare route object
            if ( $route ) {
                $route = array_pop($route);
                $cache[$command] = $route;
            }
            else {
                $cache[$command] = FALSE;
            }
            
        endif;
        
        return $cache[$command];
        
    }
    
    /**
     * Prepares the Application for handling the request
     */
    public static function boot() {
        
        // Apply system timezone
        ini_set('date.timezone', 'Asia/Kolkata');
        
        // Debug mode?
        if ( self::readSetting('system--debugMode') ) {
            ini_set('display_errors', 1);
            ini_set('error_reporting', E_ALL);
        }
        else {
            ini_set('display_errors', 0);
        }
        
        // Load modules
        \Natty::getPackage('module');
        
        // Trigger Event
        self::trigger('system--boot');
        
    }
    
    /**
     * Invokes necessary callbacks to prepare primary response
     */
    public static function execute() {
        
        self::trigger('system--beforeExecute');
        
        try {
            
            $response = self::getResponse();
            
            // Render a 404 error
            if ( !$route = self::getRoute() ):
                self::$instances['route'] = self::getEntity('system--route', 'error/%');
                throw new \Natty\Core\ControllerException(404);
            endif;
            
            // Render a 403 error
            if ( !$route->execute('perm') ):
                self::$instances['route'] = self::getEntity('system--route', 'error/%');
                throw new \Natty\Core\ControllerException(403);
            endif;
            
            // Generate heading
            $heading = $route->heading;
            if ($route->headingCallback)
                $heading = $route->execute('heading');
            
            $response->attribute('title', $heading);
            
            // Generate content
            $block = array (
                '_data' => $route->execute('content'),
                '_block' => 'system--content',
            );
            if ( FALSE === $block['_data'] )
                throw new \Natty\Core\ControllerException('An unknown error ocurred during execution!', 500);
            
        }
        catch ( \Natty\Core\ControllerException $e ) {
            $block['_data'] = \Module\System\Logic\DefaultController::pageError($e->getCode(), $e->getMessage());
        }
        
        // Store the main output
        $response->output['content']['main'] = $block;
        
        self::trigger('system--execute');
        
    }
    
    public static function render() {
        
        // Trigger Event
        self::trigger('system--beforeRender');
        
        $response = self::getResponse();
        $format = $response->attribute('format') ? : 'html';
        
        // Prepare template variables
        $data = $response->getState();
        $data = array_merge($data['variables'], $data);
        unset ($data['variables']);
        
        echo natty_render_template(array (
            '_template' => 'module/system/tmpl/response.' . $format,
            '_data' => $data,
        ));
        
        // Trigger Event
        self::trigger('system--render');
        
    }
    
    public static function terminate() {
        self::trigger('system--terminate');
    }
    
    /**
     * 
     * @param type $code
     * @param type $message
     * @throws \Natty\Core\ControllerException
     */
    public static function error($code, $message = NULL) {
        throw new \Natty\Core\ControllerException($message, $code);
    }
    
    /**
     * Triggers the specified event and invokes all module-specific listeners
     * subscribed to that event.
     * @param string $event The event to trigger
     * @param mixed $data Data to be passed to the listeners (by reference)
     */
    public static function trigger($event, &$data = NULL) {
        
        // Prepare a list of packages
        $package_coll = self::getPackage('module');
        if ($skin = self::getSkin())
            $package_coll[] = $skin;
        
        // Execute listener method
        $method = 'on' . natty_strtocamel($event, 1);
        
        foreach ( $package_coll as $package ):
            if ( method_exists($package, $method) )
                $package->{$method}($data);
        endforeach;
        
    }
    
    /**
     * Reads a configuration parameter.
     * @param string $name
     * @param mixed $fallback [optional] If no value is found for the
     * parameter, this value is returned.
     * @return mixed Value for the said parameter.
     */
    public static function readSetting($name, $fallback = NULL) {
        
        // Read database settings
        if ( is_null(self::$settings) ):
            
            self::$settings['system--installed'] = 0;
        
            $file_settings = self::readSettings();
            foreach ( $file_settings as $param_name => $param_value ):
                self::$settings[$param_name] = $param_value;
            endforeach;
            
            // Register site root for autoload
            \Importer::$directories[] = self::$settings['system--siteRoot'];
            
            unset ($file_settings, $param_name, $param_value);
            
        endif;
        
        // Load settings if Natty is installed
        if ( !isset (self::$settings[$name]) && self::$settings['system--installed'] ):
            static $loaded;
            if ( !$loaded ):
                $query = "SELECT * FROM {%__system_settings} WHERE 1=1";
                $data = self::getDbo()
                        ->execute($query)
                        ->fetchAll();
                foreach ( $data as $record ):
                    if ( $record['isSerialized'] )
                        $record['value'] = unserialize($record['value']);
                    self::$settings[$record['sid']] = $record['value'];
                endforeach;
                $loaded = TRUE;
            endif;
        endif;
        
        return isset (self::$settings[$name]) 
            ? self::$settings[$name] : $fallback;
        
    }
    
    public static function writeSetting($name, $value) {
        
        if ( self::$settings['system--installed'] ):
            
            $tablename = '%__system_settings';
            
            if ( is_null($value) ) {
                self::getDbo()->delete($tablename, array (
                    'key' => array ('sid' => $name)
                ));
            }
            else {
                $record = array (
                    'sid' => $name,
                    'value' => is_string($value) ? $value : serialize($value),
                    'isSerialized' => !is_string($value)
                );
                self::getDbo()->upsert($tablename, $record, array (
                    'keys' => array ('sid')
                ));
            }
            
        endif;
        
        self::$settings[$name] = $value;
        
    }
    
    /**
     * Read site-specific settings from a file.
     * @return array
     */
    protected static function readSettings() {
        
        // Determine active instance
        $site_parts = explode('.', $_SERVER['SERVER_NAME']);
        while ( sizeof($site_parts) > 0 ):
            
            $site_slug = implode('.', $site_parts);
        
            $settings_dirname = NATTY_ROOT . DS . 'instance/' . $site_slug;
            $settings_filename = $settings_dirname . '/settings.php';
            
            if ( is_dir($settings_dirname) || is_file($settings_filename) )
                break;
            
            array_shift($site_parts);
            
        endwhile;
            
        // Read settings file if exists
        $data = array ();
        if ( sizeof($site_parts) ) {
            if ( is_dir($settings_dirname) )
                $site_slug = basename($settings_dirname);
            if ( is_file($settings_filename) )
                include $settings_filename;
        }
        // Return blank array
        else {
            $site_slug = $_SERVER['SERVER_NAME'];
        }
        
        // Prepare derived data
        $data['system--siteSlug'] = $site_slug;
        $data['system--sitePath'] = 'instance/' . $site_slug;
        $data['system--siteRoot'] = NATTY_ROOT . '/' . $data['system--sitePath'];
        $data['system--siteBase'] = NATTY_BASE . $data['system--sitePath'];
        
        return $data;
        
    }
    
    /**
     * Writes assigned settings a file.
     * @param string $filename
     * @return boolean True on success or false on failure
     * @todo Better way to replace variables into the template
     */
    public static function writeSettings($filename) {
        
        // Prepare settings directory
        $directory = dirname($filename);
        if ( !is_dir($directory) && !mkdir($directory, 0755, TRUE) )
            natty_debug('Could not create settings directory.');

        // Read settings template
        $settings_template = NATTY_ROOT . '/common/settings.sample.php';
        if ( !is_file($settings_template) )
            die('Cannot find instance/default/settings.sample.php');
        $settings_template = file_get_contents($settings_template);

        // Replace settings variables
        $output = str_replace(array (
            "'@system--installed'",
            "'@system--database:default:driver'",
            "'@system--database:default:host'",
            "'@system--database:default:dbname'",
            "'@system--database:default:prefix'",
            "'@system--database:default:username'",
            "'@system--database:default:password'",
            "'@system--cipherKey'",
        ), array (
            self::$settings['system--installed'],
            '"' . self::$settings['system--database']['default']['driver'] . '"',
            '"' . self::$settings['system--database']['default']['host'] . '"',
            '"' . self::$settings['system--database']['default']['dbname'] . '"',
            '"' . self::$settings['system--database']['default']['prefix'] . '"',
            '"' . self::$settings['system--database']['default']['username'] . '"',
            '"' . self::$settings['system--database']['default']['password'] . '"',
            '"' . self::$settings['system--cipherKey'] . '"',
        ), $settings_template);

        if ( !file_put_contents($filename, $output) )
            throw new \Natty\Core\FilePermException();
        
        // Parameters which are set in the settings file
        $nondb_params = array (
            'system--installed',
            'system--database',
            'system--siteSlug',
            'system--sitePath',
            'system--siteRoot',
            'system--siteBase',
            'system--debugMode',
            'system--cipherKey',
        );
        
        // Save other settings to database
        if ( self::readSetting('system--installed') ):
            foreach ( self::$settings as $param_name => $param_value ):
                if ( !in_array($param_name, $nondb_params) ):
                    self::writeSetting($param_name, $param_value);
                endif;
            endforeach;
        endif;
        
    }

}