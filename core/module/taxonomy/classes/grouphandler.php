<?php

namespace Module\Taxonomy\Classes;

class GroupHandler
extends \Natty\ORM\i18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'tableName' => '%__taxonomy_group',
            'etid' => 'taxonomy--group',
            'singularName' => 'group',
            'pluralName' => 'groups',
            'keys' => array (
                'id' => 'gid',
                'label' => 'name'
            ),
            'properties' => array (
                'gid' => array (),
                'gcode' => array (),
                'module' => array (),
                'name' => array ('isTranslatable' => TRUE),
                'description' => array ('isTranslatable' => TRUE, 'default' => NULL),
                'dtCreated' => array (),
                'maxLevels' => array ('default' => NATTY_MAX_LEVELS),
                'isLocked' => array ('default' => 0),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array()) {
        if ( !isset ($data['tsCreated']) || empty ($data['tsCreated']) )
            $data['dtCreated'] = date('Y-m-d H:i:s');
        if ( !isset ($data['module']) )
            $data['module'] = 'taxonomy';
        return parent::create($data);
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        $entity->maxLevels = min($entity->maxLevels, NATTY_MAX_LEVELS);
        $entity->maxLevels = max($entity->maxLevels, 1);
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array ()) {
        
        // Delete all top-level terms (children will be deleted automatically)
        $term_coll = \Natty::getHandler('taxonomy--term')->read(array (
            'key' => array (
                'gid' => $entity->gid,
                'level' => 0,
            ),
        ));
        
        foreach ( $term_coll as $term ):
            $term->delete();
        endforeach;
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    public function readById($identifier, array $options = array()) {
        
        // Read by code?
        if ( !is_numeric($identifier) ):
            $options['key'] = array ('gcode' => $identifier);
            $options['unique'] = 1;
            return $this->read($options);
        endif;
        
        return parent::readById($identifier, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        
        $output = array ();
        
        if ( $user->can('taxonomy--administer') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode) . '">Edit</a>';
            $output['terms'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/terms') . '">Manage Terms</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/delete') . '">Delete</a>';
            
            $output['attribute'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/attribute') . '">Manage Attributes</a>';
            $output['display'] = '<a href="' . \Natty::url('backend/taxonomy/' . $entity->gcode . '/display') . '">Manage Display</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}