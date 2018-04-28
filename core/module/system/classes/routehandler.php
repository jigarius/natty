<?php

namespace Module\System\Classes;

class RouteHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'tableName' => '%__system_route',
            'etid' => 'system--route',
            'singularName' => 'route',
            'pluralName' => 'routes',
            'entityObjectClass' => 'Module\\System\\Classes\\RouteObject',
            'keys' => array (
                'id' => 'rid',
                'label' => 'heading'
            ),
            'properties' => array (
                'rid' => array ('required' => 1),
                'module' => array (),
                'pattern' => array ('sdata' => 1),
                'heading' => array (),
                'headingCallback' => array ('sdata' => 1, 'default' => NULL),
                'headingArguments' => array ('sdata' => 1, 'default' => array ()),
                'description' => array ('default' => NULL),
                'size' => array (),
                'parts' => array ('sdata' => 1, 'default' => array ()),
                'variables' => array (),
                'wildcardType' => array ('sdata' => 1, 'default' => array ()),
                'wildcardCallback' => array ('sdata' => 1, 'default' => array ()),
                'wildcardArguments' => array ('sdata' => 1, 'default' => array ()),
                'content' => array ('sdata' => 1, 'default' => NULL),
                'contentCallback' => array ('sdata' => 1, 'default' => NULL),
                'contentArguments' => array ('sdata' => 1, 'default' => array ()),
                'perm' => array ('sdata' => 1, 'default' => NULL),
                'permCallback' => array ('sdata' => 1, 'default' => 'system::routePermissionCallback'),
                'permArguments' => array ('sdata' => 1, 'default' => array ()),
                'parentId' => array ('default' => NULL),
                'ooa' => array ('default' => 500),
                'isBackend' => array ('default' => 0),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function create( array $data = array () ) {
        
        // Parse pattern if required
        if ( !isset ($data['rid']) )
            throw new \InvalidArgumentException('Primary identifier "rid" must be specified!');
        
        return parent::create($data);
        
    }
    
    public function onBeforeSave(&$entity, array $options = array ()) {
        
        // Determine parts
        $entity->parts = explode('/', $entity->rid);
        $entity->size = sizeof($entity->parts);
        $entity->variables = substr_count($entity->rid, '%');
        
        // Determine parent
        if ( !$entity->parentId ):
            $rid_parts = $entity->parts;
            array_pop($rid_parts);
            $entity->parentId = implode('/', $rid_parts);
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
}