<?php

namespace Natty\ORM;

/**
 * Basic Database Entity Handler
 */
class BasicDatabaseEntityHandler
extends EntityHandler {
    
    /**
     * Database connection
     * @var \Natty\DBAL\Base\Connection
     */
    protected $dbo;
    
    /**
     * Primary data table for the entity
     * @param string
     */
    protected $tableName;
    
    public function __construct( array $options = array () ) {
        
        parent::__construct($options);
        
        // Determine tablename
        if ( !isset ($options['tableName']) )
            $options['tableName'] = '%__' . $this->moduleCode . '_' . $this->modelCode;
        $this->tableName = $options['tableName'];
        
        // Set active connection
        if ( !isset ($options['dbo']) )
            $options['dbo'] = \Natty::getDbo();        
        $this->dbo = $options['dbo'];
        
    }
    
    /**
     * Returns an entity selection query
     * @param array $options [optional] Associative array of query options 
     * as used in EntityHandler::read();
     * @return \Natty\DBAL\SelectQuery
     */
    public function getQuery( array $options = array () ) {
        
        $tablename = $this->tableName . ' ' . $this->modelCode;
        $query = $this->dbo->getQuery('SELECT', $tablename);
        
        // Fallback options
        $options = array_merge(array (
            'key' => array (),
            'properties' => array (),
            'conditions' => array (),
            'parameters' => array (),
            'offset' => NULL,
            'limit' => NULL,
            'nocache' => TRUE,
        ), $options);
        
        // Determine columns to read
        $base_columns = $options['properties'];
        if ( 0 === sizeof($base_columns) ):
            foreach ( $this->properties as $property => $definition ):

                // Provision for loading "sdata"
                if ( isset ($definition['sdata']) ):
                    $base_columns['sdata'] = 'sdata';
                    continue;
                endif;

                $base_columns[] = $property;

            endforeach;
        endif;
        $query->addColumns($base_columns, $this->modelCode);
        
        // Apply conditions
        if ( sizeof($options['conditions']) > 0 )
            $query->addComplexCondition($options['conditions']);
        
        // Apply sorting
        if ( isset ($options['ordering']) ):
            foreach ( $options['ordering'] as $column => $order ):
                $query->orderBy($column, $order);
            endforeach;
        endif;
        
        // Apply limit and offset
        if ( !is_null($options['offset']) )
            $query->offset($options['offset']);
        if ( !is_null($options['limit']) )
            $query->limit($options['limit']);
        
        return $query;
        
    }
    
    /**
     * Executes the given Query and creates Entity instances with the
     * records returned by the query.
     * @param \Natty\DBAL\SelectQuery $query
     * @param array $options An array of options
     * @return array An array of Entity objects
     */
    public function execute($query, array $options = array ()) {
        
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // Build and execute query
        $records = $query->execute($options['parameters'])
                ->fetchAll();
        
        // Pre-process every fetched object
        $entities = array();
        $id_key = $this->getKey('id');
        foreach ( $records as $record ):
            $record = $this->prepareForUsage($record);
            $entity = $this->create($record);
            $entities[$entity->$id_key] = $entity;
        endforeach;
        
        // Trigger onRead event
        if ( sizeof($entities) )
            $this->onRead($entities, $options);
        
        return $entities;
        
    }
    
    public function prepareForStorage( &$entity ) {
        
        $record = parent::prepareForStorage($entity);
        
        foreach ( $this->properties as $property => $definition ):
            
            // Serialize "sdata" properties (if any)
            if ( isset ($definition['sdata']) ):
                
                // Prepare sdata pocket
                if ( !isset ($record['sdata']) )
                    $record['sdata'] = array ();
                
                if ( array_key_exists($property, $record) )
                    $record['sdata'][$property] = $record[$property];
                
                unset ($record[$property]);
                
            endif;
            
        endforeach;
        
        // Serialize "sdata", if any
        if ( isset ($record['sdata']) )
            $record['sdata'] = serialize($record['sdata']);
        
        return $record;
        
    }
    
    public function prepareForUsage( array $record ) {
        
        $record = parent::prepareForUsage($record);
        
        // Unserialize sdata properties
        if ( isset ($record['sdata']) ):
            $sdata = unserialize($record['sdata']);
            if ( !is_array($sdata) )
                $sdata = array ();
            unset ($record['sdata']);
            // Merge serialized properties
            $record = array_merge($record, $sdata);
        endif;
        
        return $record;
        
    }
    
    final public function getDbo() {
        return $this->dbo;
    }
    
    final public function setDbo(&$dbo) {
        $this->dbo = $dbo;
    }
    
    public function read( array $options = array () ) {
        
        // Must have conditions and parameters
        $options = array_merge(array (
            'conditions' => array (),
            'parameters' => array (),
        ), $options);
        
        // Apply key-value conditions
        if ( isset ($options['key']) ):
            $model_code = $this->getModelCode();
            foreach ( $options['key'] as $prop_name => $prop_value ):
                
                // Handle null values
                if ( is_null($prop_value) ):
                    $options['conditions'][] = array ('AND', '{' . $model_code . '}.{' . $prop_name . '} IS NULL');
                    continue;
                endif;
                
                $options['conditions'][] = array ('AND', array ($model_code . '.' . $prop_name, '=', ':key_' . $prop_name));
                $options['parameters']['key_' . $prop_name] = $prop_value;
                
            endforeach;
            unset ($options['key']);
        endif;
        
        // Build and execute query
        $query = $this->getQuery($options);
        $entities = $this->execute($query, $options);
        $query->getDbo()->reset();
        
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
    
    public function readById($identifier, array $options = array ()) {
        
        $entity_coll = array ();
        
        // Force identifier as an array
        if ( !is_array($identifier) ) {
            $identifiers = array ($identifier);
            $options['unique'] = TRUE;
        }
        else {
            $identifiers = $identifier;
        }
        
        // Read from static cache, if applicable
        if ( $this->staticCache && !isset ($options['nocache']) ):
            foreach ( $identifiers as $key => $eid ):
                if ( $entity = $this->staticCacheRead($eid) ):
                    unset ($identifiers[$key]);
                    $entity_coll[$eid] = $entity;
                endif;
            endforeach;
        endif;
        
        // Read uncached items from database
        if (sizeof($identifiers) > 0):
            
            $fresh_entities = $this->read(array (
                'conditions' => array (
                    array ('AND', array ($this->getKey('id'), 'IN', $identifiers))
                ),
            ));
            if ( is_array($fresh_entities) )
                $entity_coll += $fresh_entities;
            
            // Was everything loaded?
            if ( sizeof($identifiers) != sizeof($fresh_entities) )
                return FALSE;
            
        endif;
        
        // Return single/multiple as required
        return is_array($identifier)
            ? $entity_coll : $entity_coll[$identifier];
        
    }
    
    public function readOptions(array $options = array ()) {
        
        $id_key = $this->getKey('id');
        $label_key = $this->getKey('label');
        $parent_key = $this->getKey('parent');
        $level_key = $this->getKey('level');
        $ooa_key = $this->getKey('ooa');
        
        // Fallback options
        $options = array_merge(array (
            'valueProperty' => $id_key,
            'labelProperty' => $label_key,
            'conditions' => array (),
            'ordering' => array ($label_key => 'asc'),
            'parameters' => array (),
            'format' => 'options',
        ), $options);
        
        // Touch query options
        $this->getDbo()->touchOptions($options);
        
        $value_prop = $options['valueProperty'];
        $label_prop = $options['labelProperty'];
        
        // Read data
        $records = $this->getQuery($options)
                ->execute($options['parameters'])
                ->fetchAll();
        
        // Arrange into tree structure
        if ( $parent_key ):
            $tree_options = array (
                'idKey' => $id_key,
                'parentKey' => $parent_key,
            );
            if ( $level_key )
                $tree_options['levelKey'] = $level_key;
            if ( $ooa_key )
                $tree_options['ooaKey'] = $ooa_key;
            $records = natty_sort_tree($records, $tree_options);
        endif;
        
        // Return results as is?
        if ( 'array' === $options['format'] )
            return $records;
        
        // Return keyed by ID
        $output = array ();
        foreach ( $records as $record ):
            $this_option = array (
                '_data' => $record[$label_prop],
                'value' => $record[$value_prop],
            );
            if ( $level_key )
                $this_option['level'] = $record[$level_key];
            $output[$record[$id_key]] = $this_option;
            unset ($this_option);
        endforeach;
        
        return $output;
        
    }
    
    protected function insert( &$entity, array $options = array () ) {
        
        $record = $this->prepareForStorage($entity);
        
        // Insert regular properties
        $id = $this->getDbo()
                ->insert($this->tableName, $record);
        
        // If a Last Insert ID was returned, add it to the object
        if ( !is_bool($id) )
            $entity->setVar($this->getKey('id'), $id);
        
    }
    
    protected function update( &$entity, array $options = array () ) {
        
        $this->isIdentifiable($entity, TRUE);
        
        $record = $this->prepareForStorage($entity);
        
        // Update entity data
        $id_key = $this->getKey('id');
        $this->dbo
                ->update($this->tableName, $record, array (
                    'key' => array ($id_key => $entity->$id_key),
                ));
        
    }
    
    public function delete(&$entity, array $options = array ()) {
        
        $this->isIdentifiable($entity, TRUE);
        
        $this->onBeforeDelete($entity, $options);
        
        // Delete entity data
        $id_key = $this->getKey('id');
        $this->dbo
                ->delete($this->tableName, array (
                    'key' => array ($id_key => $entity->$id_key),
                ));
        
        // Clear static-cache
        if ( $this->staticCache )
            $this->staticCacheDelete($entity);
        
        $this->onDelete($entity, $options);
        
    }
    
}