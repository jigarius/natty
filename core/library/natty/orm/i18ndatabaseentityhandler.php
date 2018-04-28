<?php

namespace Natty\ORM;

/**
 * I18N Database Entity Handler
 */
class I18nDatabaseEntityHandler
extends BasicDatabaseEntityHandler {
    
    /**
     * Table which contains internationalization data
     * @var string
     */
    protected $i18nTableName;
    
    public $isTranslatable = TRUE;
    
    public function __construct( array $options = array () ) {
        
        // Must have language key
        if ( !isset ($options['keys']['language']) )
            $options['keys']['language'] = 'ail';
        
        parent::__construct($options);
        
        $this->i18nTableName = $this->tableName . '_i18n';
        
    }
    
    public function create(array $data = array()) {
        $lang_key = $this->getKey('language');
        if ( !isset ($data[$lang_key]) )
            $data[$lang_key] = \Natty::getInputLangId();
        return parent::create($data);
    }
    
    protected function getColumns( $translatable = FALSE ) {
        $flag = $translatable
                ? 'i18n' : 'regular';
        static $cache;
        if ( !is_array($cache) )
            $cache = array ();
        if ( !isset ($columns[$flag]) ):
            $output = array ();
            foreach ( $this->properties as $property => $definition ):
                if ( $translatable ) {
                    if ( isset ($definition['isTranslatable']) )
                        $output[] = $property;
                }
                else {
                    if ( isset ($definition['sdata']) ):
                        $output['sdata'] = 'sdata';
                        continue;
                    endif;
                    if ( !isset ($definition['isTranslatable']) )
                        $output[] = $property;
                }
            endforeach;
            $cache[$flag] = $output;
        endif;
        return $cache[$flag];
    }
    
    /**
     * Returns an entity selection query
     * @param array $options [optional] Associative array of query options 
     * as used in EntityHandler::read();
     * @return \Natty\DBAL\SelectQuery
     */
    public function getQuery( array $options = array () ) {
        
        $base_columns = $this->getColumns();
        $id_key = $this->getKey('id');
        $lang_key = $this->getKey('language');
        
        // Join data for translatable attributes
        $join_columns = $this->getColumns(TRUE);
        $join_tablename = $this->i18nTableName;
        $join_alias = $this->modelCode . '_i18n';
        $join_conditions = array (
            array ('AND', array ($join_alias . '.' . $id_key, '=', $this->modelCode . '.' . $id_key)),
            array ('AND', array ($join_alias . '.' . $lang_key, '=', ':ail'))
        );
        
        // Ignore columns not mentioned in options
        if ( isset ($options['properties']) ):
            
            $join_columns = array_intersect($join_columns, $options['properties']);
            $base_columns = array_intersect($base_columns, $options['properties']);
            
            if ( isset ($this->properties['sdata']) )
                $base_columns['sdata'] = 'sdata';
            
        endif;
        
        $query = $this->dbo
                ->getQuery('SELECT', $this->tableName . ' ' . $this->modelCode)
                ->addColumns($base_columns, $this->modelCode)
                ->addExpression(":ail {{$lang_key}}")
                ->addColumns($join_columns, $join_alias)
                ->addJoin('LEFT', $join_tablename . ' ' . $join_alias, $join_conditions);
        
        // Apply conditions
        if ( isset ($options['conditions']) && sizeof($options['conditions']) > 0 )
            $query->addComplexCondition($options['conditions']);
        
        // Apply sorting
        if ( isset ($options['ordering']) ):
            foreach ( $options['ordering'] as $column => $order ):
                $query->orderBy($column, $order);
            endforeach;
        endif;
        
        // Apply limit and offset
        if ( isset ($options['offset']) )
            $query->offset($options['offset']);
        if ( isset ($options['limit']) )
            $query->limit($options['limit']);
        
        return $query;
        
    }
    
    public function execute($query, array $options = array ()) {
        
        // Must specify parameters
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // Specify a default language for translatable properties
        if ( !isset ($options['language']) )
            $options['language'] = \Natty::getOutputLangId();
        $options['parameters']['ail'] = $options['language'];
        
        // Build and execute query
        $records = $query
                ->execute($options['parameters'])
                ->fetchAll(\PDO::FETCH_ASSOC);
        
        // Pre-process every fetched object
        $entity_coll = array ();
        $id_key = $this->getKey('id');
        foreach ( $records as $record ):
            
            $record = $this->prepareForUsage($record);
            $entity = $this->create($record);
            $entity_coll[$entity->$id_key] = $entity;
            
        endforeach;
        
        // Trigger onRead event
        if ( sizeof($entity_coll) > 0 )
            $this->onRead($entity_coll, $options);
        
        return $entity_coll;
        
    }
    
    public function readById( $identifier, array $options = array () ) {
        
        $entities = array ();
        
        // Determine read language
        if ( !isset ($options['language']) ):
            $options['language'] = \Natty::getOutputLangId();
        endif;
        
        // Force identifier as an array
        if ( !is_array($identifier) ) {
            $identifiers = array ($identifier);
        }
        else {
            $identifiers = $identifier;
        }
        
        // Read from static cache, if applicable
        if ( $this->staticCache && !isset ($options['nocache']) ):
            foreach ( $identifiers as $key => $eid ):
                if ( $entity = $this->staticCacheRead(array ($eid, $options['language'])) ):
                    unset ($identifiers[$key]);
                    $entities[$eid] = $entity;
                endif;
            endforeach;
        endif;
        
        // Read uncached items from database
        if ( sizeof($identifiers) > 0 ):
            
            $options['conditions'] = array (
                array ('AND', array ($this->getModelCode() . '.' . $this->getKey('id'), 'IN', $identifiers)),
            );
            $fresh_entities = $this->read($options);
            if ( is_array($fresh_entities) )
                $entities += $fresh_entities;
            
            // Was everything loaded?
            if ( sizeof($identifiers) != sizeof($fresh_entities) )
                return FALSE;
            
        endif;
        
        // Return single/multiple as required
        return is_array($identifier)
            ? $entities : $entities[$identifier];
        
    }
    
    public function read(array $options = array ()) {
        
        // Must have conditions and parameters
        $options = array_merge(array (
            'conditions' => array (),
            'parameters' => array (),
        ), $options);
        
        // Apply key-value conditions
        if ( isset ($options['key']) ):
            $model_code = $this->getModelCode();
            foreach ( $options['key'] as $prop_name => $prop_value ):
                $prop_definition = $this->properties[$prop_name];
                $prop_tablename = ( isset ($prop_definition['isTranslatable']) )
                        ? $model_code . '_i18n' : $model_code;
                $options['conditions'][] = array ('AND', array ($prop_tablename . '.' . $prop_name, '=', ':key_' . $prop_name));
                $options['parameters']['key_' . $prop_name] = $prop_value;
            endforeach;
            unset ($options['key'], $model_code, $prop_definition, $prop_tablename);
        endif;
        
        // Build and execute query
        $query = $this->getQuery($options);
        $entities = $this->execute($query, $options);
        
        // Static cache
        if ( $this->staticCache && !isset ($options['nocache']) ):
            foreach ( $entities as $entity ):
                $this->staticCacheInsert($entity);
            endforeach;
        endif;
        
        // Read a unique object?
        if ( isset ($options['unique']) ):
            return ( 1 != sizeof ($entities) )
                ? FALSE : $entity;
        endif;
        
        return $entities;
        
    }
    
    protected function insert( &$entity, array $options = array () ) {
        
        $id_key = $this->getKey('id');
        $lang_key = $this->keys['language'];
        
        // Must contain translation in default language
        if ( !$entity->getVar($lang_key) )
            throw new \LogicException('Attempt to create entity without specifying a language.');
        
        $record = $this->prepareForStorage($entity);
        
        // Prepare base properties
        $base_columns = $this->getColumns(FALSE);
        $base_columns = array_flip($base_columns);
        $base_record = array_intersect_key($record, $base_columns);
        
        // Insert base properties
        $id = $this->dbo->insert($this->tableName, $base_record);
        
        // If a Last Insert ID was returned, add it to the object
        if ( !is_bool($id) ):
            $record[$id_key] = $id;
            $entity->setVar($id_key, $id);
        endif;
        
        // Prepare translatable properties
        $i18n_columns = $this->getColumns(TRUE);
        $i18n_columns = array_flip($i18n_columns);
        $i18n_record = array_intersect_key($record, $i18n_columns);
        
        // Add key data
        $i18n_record[$id_key] = $entity->$id_key;
        $i18n_record[$lang_key] = $entity->$lang_key;
        
        // Inset i18n properties
        $this->dbo->insert($this->i18nTableName, $i18n_record);
        
    }
    
    protected function update( &$entity, array $options = array () ) {
        
        $this->isIdentifiable($entity, TRUE);
        
        $id_key = $this->getKey('id');
        $lang_key = $this->keys['language'];
        
        $record = $this->prepareForStorage($entity);
        
        // Prepare base properties
        $base_columns = $this->getColumns(FALSE);
        $base_columns = array_flip($base_columns);
        $base_record = array_intersect_key($record, $base_columns);
        
        // Update base properties
        $this->dbo->update($this->tableName, $base_record, array (
            'key' => array (
                $id_key => $entity->$id_key
            ),
        ));
        
        // Prepare translatable properties
        $i18n_columns = $this->getColumns(TRUE);
        $i18n_columns = array_flip($i18n_columns);
        $i18n_record = array_intersect_key($record, $i18n_columns);
        
        // Add key data
        $i18n_record[$id_key] = $entity->$id_key;
        $i18n_record[$lang_key] = $entity->$lang_key;
        
        // Upsert translatable properties
        $this->dbo->upsert($this->i18nTableName, $i18n_record, array (
            'keys' => array ($id_key, $lang_key),
        ));
        
    }
    
    public function delete( &$entity, array $options = array () ) {
        
        $this->onBeforeDelete($entity, $options);
        
        // Delete base properties
        $id_key = $this->getKey('id');
        $this->dbo->delete($this->tableName, array (
            'key' => array ($id_key => $entity->$id_key)
        ));
        
        // Delete translations
        $this->dbo->delete($this->i18nTableName, array (
            'key' => array ($id_key => $entity->$id_key)
        ));
        
        $this->onDelete($entity, $options);
        
    }
    
    public function readOptions(array $options = array()) {
        
        // Must specify parameters
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // Touch query options
        $this->getDbo()->touchOptions($options);
        
        // Specify language
        $lang_key = $this->keys['language'];
        if ( !isset ($options['parameters'][$lang_key]) ):
            $options['parameters'][$lang_key] = \Natty::getOutputLangId();
        endif;
        
        return parent::readOptions($options);
        
    }
    
    public function staticCacheInsert( &$entity ) {
        if ( !$eid = $entity->getId() )
            return;
        if ( !$ail = $entity->getVar($this->languageKey) )
            return;
        natty_debug(compact('eid', 'ail'));
        if ( !isset (self::$cache[$this->etid][$eid]) )
            self::$cache[$this->etid][$eid] = array ();
        self::$cache[$this->etid][$eid][$ail] = $entity;
    }
    
    public function staticCacheRead( $key ) {
        if ( !is_array($key) )
            throw new \InvalidArgumentException('Argument must be an array containing Entity ID and Language ID');
        list ($eid, $ail) = $key;
        if ( isset (self::$cache[$this->etid][$eid]) && isset (self::$cache[$this->etid][$eid][$ail]) )
            return self::$cache[$this->etid][$eid][$ail];
    }
    
}