<?php

namespace Module\Taxonomy\Classes;

use Module\Eav\Classes\AttributeHandler;

class TermHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = natty_array_merge_nested(array (
            'tableName' => '%__taxonomy_term',
            'etid' => 'taxonomy--term',
            'modelName' => array ('term', 'terms'),
            'keys' => array (
                'id' => 'tid',
                'label' => 'name',
                'parent' => 'parentId',
                'level' => 'level',
                'ooa' => 'ooa',
            ),
            'properties' => array (
                'tid' => array (),
                'gid' => array (
                    'required' => 1,
                ),
                'gcode' => array (
                    'required' => 1,
                ),
                'module' => array (
                    'required' => 1,
                ),
                'name' => array ('isTranslatable' => TRUE),
                'description' => array ('isTranslatable' => TRUE, 'default' => NULL),
                'parentId' => array ('default' => 0),
                'ooa' => array ('default' => 0),
                'level' => array ('default' => 0),
                'dtCreated' => array (),
                'isLocked' => array ('default' => 0),
                'status' => array ('default' => 1),
            )
        ), $options);
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        if ( !isset ($data['dtCreated']) || empty ($data['dtCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        
        // Load gcode
        $tgroup = \Natty::getEntity('taxonomy--group', $entity->gid);
        if ( !$tgroup )
            throw new \InvalidArgumentException('Required index "gid" has an invalid value');
        $entity->gcode = $tgroup->gcode;
        $entity->module = $tgroup->module;
        
        // Determine level
        $entity->level = 0;
        if ( $entity->parentId ):
            $parent = $this->readById($entity->parentId);
            $entity->level = $parent->level+1;
        endif;
        
        // Level cannot exceed 5
        if ( $entity->level > NATTY_MAX_LEVELS || $entity->level > $tgroup->maxLevels )
            throw new \RuntimeException('Term level exceeded maximum allowed level.');
        
        // Put the term at the end
        if ( !$entity->ooa ):
            $order = \Natty::getDbo()
                ->getQuery('select', '%__taxonomy_term')
                ->addColumn('ooa')
                ->addSimpleCondition('gid', ':gid')
                ->addSimpleCondition('parentId', ':parentId' )
                ->limit(1)
                ->execute(array ('gid' => $entity->gid, 'parentId' => $entity->parentId))
                ->fetchColumn();
            $entity->ooa = $order ? $order + 5 : 5;
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public function onBeforeDelete(&$entity, array $options = array()) {
        
        // Delete all children
        $term_coll = $this->read(array (
            'key' => array (
                'gid' => $entity->gid,
                'parentId' => $entity->tid,
            )
        ));
        
        foreach ( $term_coll as $term ):
            $term->delete();
        endforeach;
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        AttributeHandler::attachEntitySave($this->etid, $entity->gid, $entity, $options);
        parent::onSave($entity, $options);
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        AttributeHandler::attachEntityDelete($this->etid, $entity->gid, $entity, $options);
        parent::onDelete($entity, $options);
    }
    
    protected function onRead(array &$entities, array $options = array ()) {
        AttributeHandler::attachEntityRead($this->etid, $entities, $options);
        parent::onRead($entities, $options);
    }
    
    public function getUri($entity) {
        
        static $cache;
        if ( !is_array($cache) )
            $cache = array ();
        
        $module_code = $entity->module;
        
        // Determine URI callback
        if ( !isset ($cache[$module_code]) ):
            $callback_classname = '\\Module\\' . ucfirst($module_code) . '\\Controller';
            $callback_method = 'taxonomyTermUri';
            if ( method_exists($callback_classname, $callback_method) ) {
                $cache[$module_code] = array ($callback_classname, $callback_method);
            }
            else {
                $cache[$module_code] = FALSE;
            }
        endif;
        
        // Return URI
        if ( $cache[$module_code] )
            return call_user_func($cache[$module_code], $entity);
        
        return parent::getUri($entity);
        
    }
    
    public function buildContent(&$entity, $options = array()) {
        
        parent::buildContent($entity, $options);
        AttributeHandler::attachEntityView($this->etid, $entity->gid, $entity, $options);
        
        if ( $entity->description )
            $entity->build['description'] = $entity->description;
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $output = array ();
        $user = \Natty::getUser();
        
        if ( $user->can('taxonomy--administer') ):
            $output['edit'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/terms/' . $entity->tid) . '">Edit</a>';
            if (!$entity->isLocked)
                $output['delete'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/terms/' . $entity->tid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    public function getEntityGroupData( $rebuild = FALSE ) {
        
        static $cache;
        
        if ( !is_array($cache) || $rebuild ):
            
            $cache = array ();
            
            $tgroup_coll = \Natty::getHandler('taxonomy--group')
                    ->read();
            
            foreach ( $tgroup_coll as $tgroup ):
                $cache[$tgroup->gid] = array (
                    'id' => $tgroup->gid,
                    'name' => $tgroup->name,
                    'uri' => 'backend/taxonomy/' . $tgroup->gcode,
                );
            endforeach;
        
        endif;
        
        return $cache;
        
    }
    
}