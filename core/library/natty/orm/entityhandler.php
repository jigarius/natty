<?php

namespace Natty\ORM;

abstract class EntityHandler
extends \Natty\StdClass {
    
    /**
     * An array containing Entity Handler instances
     * @var array
     */
    protected static $instances = array ();
    
    /**
     * Code of the module to which the entity belongs
     * @var string
     */
    protected $moduleCode;
    
    /**
     * Unique code of the entity within the module
     * @var string
     */
    protected $modelCode;
    
    /**
     * A name used to refer to a single instance of the Entity
     * @var string
     */
    protected $modelName = array ('item', 'items');
    
    /**
     * Entity property definitions
     * @var array
     */
    protected $properties = array ();
    
    /**
     * Site-wide unique ID for this entity type
     * @var string
     */
    protected $etid;
    
    /**
     * Class for the model objects - Defaults to \Natty\ORM\Entity
     * @var string
     */
    protected $entityObjectClass;
    
    /**
     * Default properties for model objects
     * @var array
     */
    protected $entityDefaultState;
    
    /**
     * Key data for the entity type - Example id, name, language, etc.
     * @var array
     */
    protected $keys = array (
        'id' => FALSE,
        'label' => FALSE,
    );
    
    /**
     * Whether this entity type supports EAV
     * @var bool
     */
    public $isAttributable = FALSE;
    
    /**
     * Base URI for the entity-type
     * @var string
     */
    protected $uri;
    
    /**
     * An array of loaded entities, cached statically for reuse
     * @var array
     */
    protected static $cache = array ();
    
    /**
     * Creates an EntityHandler with the given options
     * @param array $options An array of options
     * @throws \InvalidArgumentException If options are improper
     */
    protected function __construct( array $options = array () ) {
        
        // Validate unique machine name
        if ( !isset ($options['etid']) )
            throw new \InvalidArgumentException('Required index "etid" not defined');
        
        // Keys must be specified
        if ( !isset ($options['keys']) )
            throw new \InvalidArgumentException('Required index "keys" not defined');
        
        // Validate id Field
        if ( !isset ($options['keys']['id']) )
            throw new \InvalidArgumentException('Required key "id" not defined');
        
        // Label field defaults to "name"
        if ( !isset ($options['keys']['label']) )
            $options['keys']['label'] = 'name';
        
        // Retrieve module and model code
        $parts = explode('--', $options['etid']);
        if ( 2 != sizeof($parts) )
            throw new \InvalidArgumentException('Invalid "etid" format for "' . $options['etid'] . '". Must be module--model.');
        list ($this->moduleCode, $this->modelCode) = $parts;
        
        // Validate model class
        if ( !isset ($options['entityObjectClass']) )
            $options['entityObjectClass'] = __NAMESPACE__ . '\\EntityObject';
        
        // Determine Base URI
        if ( !isset ($options['uri']) )
            $options['uri'] = str_replace('--', '/', $options['etid']);
        
        // See if staticCache is enaled
        if ( !isset ($options['staticCache']) )
            $options['staticCache'] = true;
        if ( $options['staticCache'] )
            self::$cache[$options['etid']] = array ();
        
        $this->setState($options);
        
    }
    
    public function __clone() {
        throw new \LogicException('Cannot clone a singleton object!');
    }
    
    /**
     * An entity with the given state data
     * @param array $data [optional] Entity state
     * @return \Natty\ORM\EntityObject
     */
    public function create( array $data = array () ) {
        
        $defaults = $this->getEntityDefaultState();
        
        // Merge data with defaults
        $data = sizeof($data)
                ? natty_array_merge_nested($defaults, $data)
                : $defaults;
        
        // Attach a reference to the handler and return a new Entity
        $data['handler'] =& $this;
        return new $this->entityObjectClass($data);
        
    }
    
    /**
     * Creates an Entity from the given raw data and inserts it
     * @return EntityObject
     */
    public function createAndSave( array $data ) {
        $entity = $this->create($data);
        $this->save($entity);
        return $entity;
    }
    
    public function getEntityDefaultState() {
        if ( !is_array($this->entityDefaultState) ):
            
            $this->entityDefaultState = array ();
        
            foreach ( $this->properties as $property => $definition ):
                if ( isset ($definition['default']) )
                    $this->entityDefaultState[$property] = $definition['default'];
            endforeach;
            
            if ( isset ($this->properties['dtCreated']) )
                $this->entityDefaultState['dtCreated'] = date('Y-m-d H:i:s');
            
        endif;
        return $this->entityDefaultState;
    }
    
    /**
     * Returns the general classname for the entity type
     * @return string
     */
    public function getEntityClass() {
        return $this->entityObjectClass;
    }
    
    public function getEntityTypeId() {
        return $this->etid;
    }
    
    public function getEntityCode($entity) {
        $key = $this->getKey('code');
        if (!$key)
            return FALSE;
        return $entity->$key;
    }
    
    public function getEntityLabel($entity) {
        $key = $this->getKey('label');
        if (!$key)
            return FALSE;
        return $entity->$key;
    }
    
    public function getModuleCode() {
        return $this->moduleCode;
    }
    
    public function getModelCode() {
        return $this->modelCode;
    }
    
    /**
     * Returns a human-readable name for the model based on the number of
     * objects being dealt with - If $count is 1, it returns a singular name;
     * otherwise returns its plural name.
     * @param int $count [optional] Number of items - Defaults to 1, thereby 
     * returning the singular name.
     * @return string Singular or plural name.
     */
    final public function getModelName( $count = 1 ) {
        return 1 === (int) $count
                ? $this->modelName[0] : $this->modelName[1];
    }
    
    /**
     * This method is supposed to return the disk storage path for the files
     * of a given entity type. Output is relative to the website instance's 
     * root directory.
     * @param EntityObject $entity An entity object or derivative
     * @return string Path to storage directory
     * @throws \InvalidArgumentException If the Entity does not have an ID.
     */
    public function getStorageDirname($entity) {
        $this->isIdentifiable($entity, TRUE);
        return 'files/' . str_replace('--', '/', $this->etid) . '/' . $eid;
    }
    
    /**
     * Returns the URI for a given entity.
     * @param EntityObject A loaded entity object with a proper identifier
     * @return string URI to the given entity.
     */
    public function getUri($entity) {
        $command = $this->uri . '/' . $entity->getId();
        return \Natty::url($command);
    }
    
    /**
     * Returns a singleton instance of an handler for the given entity type
     * @param string $type Entity type in the format module/model
     * @return EntityHandler
     */
    final public static function &getInstance( $type ) {
        if ( !isset (self::$instances[$type]) ):
            
            // Validate entity code
            $parts = explode('--', $type);
            if ( 2 != sizeof($parts) )
                throw new \InvalidArgumentException('Argument 1 "' . $type . '" is not in the format module--model.');

            // Return a handler    
            $classname = 'Module\\' . ucfirst($parts[0]) . '\\Classes\\' . ucfirst($parts[1]) . 'Handler';
            
            self::$instances[$type] = new $classname();
            
        endif;
        return self::$instances[$type];
    }
    
    final public function getKey($index) {
        return isset ($this->keys[$index])
            ? $this->keys[$index] : NULL;
    }
    
    /**
     * Returns definition for a given property or all properties
     * @param string $name [optional] The particular property whose definition
     * is required.
     * @return array
     */
    final public function getPropertyDefinition($name = NULL) {
        return ( is_null($name) )
            ? $this->properties : $this->properties[$name];
    }
    
    /**
     * Returns an array containing Group definitions for this entity type
     * @return array|false
     */
    public function getEntityGroupData() {
        return FALSE;
    }
    
    /**
     * Attempts to insert an Entity into the database
     * @param \Natty\ORM\EntityObject $entity Object to be inserted
     */
    abstract protected function insert( &$entity );
    
    /**
     * Tells whether the given object is an Entity or a derivative thereof
     * @param object $entity
     * @param bool $throw [optional] Whether to throw an exception on error
     * @return bool True if yes or false if no
     */
    final public static function isEntity( $entity, $throw = FALSE ) {
        $output = is_a($entity, '\\Natty\\ORM\\Entity');
        if ( !$output && $throw )
            throw new \InvalidArgumentException('Attempt to perform an Entity operation on non-entity!');
        return $output;
    }
    
    protected function isIdentifiable( $entity, $throw_exception = FALSE ) {
        $output = (bool) $entity->getVar($this->keys['id']);
        if ( !$output && $throw_exception )
            throw new \LogicException('Probable call to method with an unidentifiable entity when an identifiable entity is expected.');
        return $output;
    }
    
    /**
     * 
     * @param \Natty\ORM\EntityObject $entity
     * @return array Storage friendly entity state
     * @throws \InvalidArgumentException
     */
    public function prepareForStorage(&$entity) {
        
        $entity_state = $entity->getState();
        $record = array ();
        
        // Remove handler reference
        unset ($entity_state['handler']);
        
        // Touch properties
        foreach ( $this->properties as $property => $definition ):
            
            // If property is not set and a default value exists, apply it
            if ( array_key_exists('default', $definition) ):
                // Property not set in the entity?
                if ( !array_key_exists($property, $entity_state) )
                    $entity_state[$property] = $definition['default'];
                // Property set, but empty? Assign default
                else {
                    if ( is_string($entity_state[$property]) && 0 === strlen($entity_state[$property]) )
                        $entity_state[$property] = $definition['default'];
                }
            endif;
            
            // If property has been set, it will be exported
            if ( array_key_exists($property, $entity_state) )
                $record[$property] = $entity_state[$property];
            
            // Serialized data?
            if ( isset ($definition['serialized']) && array_key_exists($property, $entity_state) ):
                $record[$property] = serialize($record[$property]);
            endif;
            
        endforeach;
        
        return $record;
        
    }
    
    /**
     * Prepares a database record for an entity into data for Entity instance
     * @param array $record Entity data
     * @return array Prepared entity data
     */
    public function prepareForUsage( array $record ) {
        
        // Touch properties
        foreach ( $this->properties as $property => $definition ):
            
            // Serialized data?
            if ( isset ($definition['serialized']) && $record[$property] ):
                $record[$property] = unserialize($record[$property]);
                if ( FALSE === $record[$property] )
                    $record[$property] = array ();
            endif;
            
        endforeach;
        
        return $record;
        
    }
    
    final public function raiseMessage( $message, array $options = array () ) {
        $message = natty_text($message, array (
            'item' => $this->modelName[0],
            'items' => $this->modelName[1],
        ));
        \Natty\Console::message($message, $options);
    }
    
    abstract public function readById( $identifier, array $options = array () );
    
    /**
     * Finds and returns entities representing records which have matching $keys
     * @param array $keys Keys for filtering
     * @param array $options [optional] Additional options
     * @todo Do we remove this and use option "key" with read() method instead?
     * @return array|EntityObject
     */
    public function readByKeys( array $keys, array $options = array () ) {
        
        // Convert key data to options
        if (!isset ($options['key']))
            $options['key'] = array ();
        
        // Add key-value conditions
        foreach ($keys as $t_key => $value):
            $options['key'][$t_key] = $value;
        endforeach;
        
        return $this->read($options);
        
    }
    
    /**
     * Selects objects or object based on the options specified
     * @param array $options [optional] An associative array of options,
     * including but not limited to:<br />
     * conditions: An array of conditions to use for fetching<br />
     * key: An associative array of key-value type conditions<br />
     * parameters: An associative array of parameters for replacement in conditions<br />
     * ordering: Specify ordering of fetched Entities<br />
     * offset: The number of Entities to skip from start<br />
     * limit: The number of Entities to fetch<br />
     * nocache: Avoids lookup in static cache and reloads the entity.
     * @return \Natty\ORM\EntityObject|false
     */
    abstract public function read( array $options = array () );
    
    /**
     * Reads basic data about existing entities for use with Form API.
     * @see \Natty\ORM\EntityHandler::read()
     * @param array $options [optional] Options for option generation.
     * Supports relevant options supported by the read() method and these:<br />
     * valueProperty: The property to be used as identifiers<br />
     * labelProperty: The property to be used as label.
     * @return array Options as specified
     */
    abstract public function readOptions( array $options = array () );
    
    /**
     * Inserts on updates a record depending on its primary key data
     * @param EntityObject $entity The object to update or insert
     * @param array $options [optional] An associative array of options which
     * may include:<br />
     * insert: Whether to force insert even if the entity has an id;<br />
     * languages: An array of Language IDs in which to save the data
     */
    public function save(&$entity, array $options = array ()) {
        
        $entity->isNew = isset ($entity->isNew) || !$this->isIdentifiable($entity);
        
        $this->onBeforeSave($entity, $options);
        
        // Run validation
        $this->validate($entity);
        
        if ( $entity->isNew ) {
            $this->insert($entity);
        }
        else {
            $this->update($entity);
        }
        
        $this->onSave($entity, $options);
        
        unset ($entity->isNew);
        
    }
    
    /**
     * Updates an entity
     * @param array $data The record to be updated
     */
    abstract protected function update(&$entity);
    
    /**
     * Deletes an entity
     * @param type $entity
     * @param array $options
     */
    abstract public function delete(&$entity, array $options = array ());
    
    protected function onRead(array &$entities, array $options = array ()) {}
    
    protected function onBeforeDelete( &$entity, array $options = array () ) {
        $event_general = 'system/beforeEntityDelete';
        \Natty::trigger($event_general, $entity);
        $event_specific = $this->moduleCode . '/before' . ucfirst($this->modelCode) . 'Delete';
        \Natty::trigger($event_specific, $entity);
    }
    
    protected function onDelete( &$entity, array $options = array () ) {
        $event_general = 'system/entityDelete';
        \Natty::trigger($event_general, $entity);
        $event_specific = $this->moduleCode . '/' . ucfirst($this->modelCode) . 'Delete';
        \Natty::trigger($event_specific, $entity);
    }
    
    protected function onBeforeSave( &$entity, array $options = array () ) {
        $event_general = 'system/beforeEntitySave';
        \Natty::trigger($event_general, $entity);
        $event_specific = $this->moduleCode . '/before' . ucfirst($this->modelCode) . 'Save';
        \Natty::trigger($event_specific, $entity);
    }
    
    protected function onSave( &$entity, array $options = array () ) {
        $event_general = 'system/entitySave';
        \Natty::trigger($event_general, $entity);
        $event_specific = $this->moduleCode . '/' . ucfirst($this->modelCode) . 'Save';
        \Natty::trigger($event_specific, $entity);
    }
    
    /**
     * Validates an entity before saving it to the database
     * @param EntityObject $entity
     * @param array $options [optional]
     * @throws EntityException If entity data is not valid
     */
    public function validate($entity, array $options = array ()) {
        
        foreach ( $this->properties as $prop => $defi ):
            
            if ( isset ($defi['required']) ):
                if ( !isset ($entity->$prop) || empty ($entity->$prop) )
                    throw new EntityException('Required property "' . $prop . '" not assigned.');
            endif;
            
        endforeach;
        
    }
    
    /**
     * Returns an array of anchor tags leading to backend pages for the given 
     * entity. These are usually used in backend and frontend context menus.
     * Example: Edit, Clone, Delete, etc.
     * @param EntityObject $entity
     * @param array $options [optional] Array of options.
     * @return array An array containing markup for anchor tags.
     */
    public function buildBackendLinks(&$entity, array $options = array ()) {
        
        $data = array (
            'etid' => $this->etid,
            'entity' => $entity,
            'links' => array (),
        );
        \Natty::trigger($this->etid . 'BuildBackendLinks', $data);
        
        return $data['links'];
        
    }
    
    /**
     * Returns an array of anchor tags leading to frontend pages for the given 
     * entity. These are usually used in backend and frontend context menus.
     * Example: View, Download, Play, etc. Administrative tasks are not listed
     * in this list.
     * @param EntityObject $entity
     * @param array $options [optional] Array of options.
     * @return array An array containing markup for anchor tags.
     */
    public function buildFrontendLinks(&$entity, array $options = array ()) {
        
        $data = array (
            'entity' => $entity,
            'links' => array (),
        );
        \Natty::trigger($this->etid . 'BuildFrontendLinks', $data);
        
        return $data['links'];
        
    }
    
    public function buildContent(&$entity, $options = array ()) {
        
        if ( !isset ($entity->build) )
            $entity->build = array ();
        
        // Trigger an event
        $data = array (
            'entity' => &$entity,
            'options' => $options,
        );
        \Natty::trigger($this->etid . 'BuildContent', $data);
        
    }
    
    /**
     * Checks whether a given user is allowed to perform a given action on
     * a given entity object.
     * @param EntityObject $entity
     * @param string $action
     * @param mixed $action UserObject or User ID
     * @return boolean TRUE if allowed, FALSE if denied
     */
    public function allowAction($entity, $action, $user = NULL) {
        return FALSE;
    }
    
    /**
     * Renders the given entity and returns generated markup.
     * @param EntityObject $entity
     * @param array $options [optional] An array of rendering options:<br />
     * page: Whether the entity will be viewed as a full page.<br />
     * viewMode: The mode in which the entity will be viewed. Example: preview,
     * default, etc. Defaults to "default".<br />
     * nocache: If static cache should not be used (defaults to FALSE).<br />
     * _template: An array of template suggestions to supplement the
     * default template patterns. By default, the following templates are
     * looked up (in order of priority):<br />
     * [template suggestions passed as argument]<br />
     * ...<br />
     * MODULE/tmpl/MODEL.tmpl.php<br />
     * MODULE/tmpl/MODEL-EGID.tmpl.php<br />
     * system/tmpl/entity.tmpl.php<br />
     * @return string Markup for the entity.
     */
    public function render(&$entity, array $options = array ()) {
        
        // Prepare static cache
        static $cache;
        if ( !is_array($cache) )
            $cache = array ();
        
        // Option defaults
        $options = array_merge(array (
            '_template' => array (),
            'page' => FALSE,
            'viewMode' => 'default',
            'nocache' => FALSE,
            'links' => NULL,
            'variables' => array (),
        ), $options);
        
        // Show links in preview mode
        if ( is_null($options['links']) && 'preview' === $options['viewMode'] )
            $options['links'] = TRUE;
        
        // Determine cache key
        $id_key = $this->getKey('id');
        $cache_key = $entity->$id_key . ':' . $options['viewMode'];
        if ( !isset ($cache[$cache_key]) || $options['nocache'] ):
            
            $variables = $options['variables'];
            $variables['entity'] = $entity;
            $variables['url'] = $this->getUri($entity);
            $variables['heading'] = '<h3><a href="' . $variables['url'] . '">' . $entity->getLabel() . '</a></h3>';
            $variables['options'] = $options;
            $variables['classesArray'] = array (
                'n-entity',
                'view-mode-' . $options['viewMode'],
                'entity-type-' . $this->etid,
            );
            $variables['links'] = array (
                '_render' => 'element',
                '_element' => 'div',
                '_data' => $this->buildFrontendLinks($entity, $options),
                'class' => array ('n-links'),
            );
            
            // Trigger a content build event
            $entity->build = array ();
            $this->buildContent($entity, $options);
            $variables['build'] = $entity->build;
            unset ($entity->build, $options['variables']);
            
            // Sort build variables
            uasort($variables['build'], 'natty_compare_ooa');
            
            // Touch template variables
            \Natty::trigger($this->etid . 'BeforeRender', $variables);
            
            // Determine template path
            $tmpl_path = 'module/' . $this->moduleCode . '/tmpl/' . $this->modelCode;
            
            // Entity group template override
            if ( $group_key = $this->getKey('group') ):
                $variables['classesArray'][] = 'entity-group-' . $entity->$group_key;
                $options['_template'][]= $tmpl_path . '.' . $entity->$group_key . '.tmpl';
            endif;
            
            // Default template for entity type
            $options['_template'][]= $tmpl_path . '.tmpl';
            
            // Default template for entities in general
            $options['_template'][]= 'module/system/tmpl/entity.tmpl';
            
            // Flatten arrays
            $variables['classes'] = implode(' ', $variables['classesArray']);
            
            // Do the actual rendering of the template
            $cache[$cache_key] = natty_render_template(array (
                '_template' => $options['_template'],
                '_data' => $variables,
            ));
            
        endif;
        
        return $cache[$cache_key];
        
    }
    
    public function staticCacheInsert( &$entity ) {
        if ( !$eid = $entity->getId() )
            return;
        self::$cache[$this->etid][$eid] = $entity;
    }
    
    public function staticCacheRead( $key ) {
        if ( isset (self::$cache[$this->etid][$key]) )
            return self::$cache[$this->etid][$key];
    }
    
    public function staticCacheDelete( &$entity ) {
        $eid = $entity->getId();
        unset (self::$cache[$this->etid][$eid]);
    }
    
    final public function staticCacheTruncate() {
        self::$cache[$this->etid] = array ();
    }
    
}