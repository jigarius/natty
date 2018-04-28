<?php

namespace Module\Cms\Classes;

class ContenttypeHandler 
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct() {
        parent::__construct(array (
            'tableName' => '%__cms_contenttype',
            'etid' => 'cms--contenttype',
            'keys' => array (
                'id' => 'ctid',
                'label' => 'name',
            ),
            'singularName' => 'content type',
            'pluralName' => 'content types',
            'uri' => 'cms/content-type',
            'properties' => array (
                'ctid' => array (),
                'module' => array (),
                'name' => array (),
                'description' => array ('default' => NULL),
                'isCustom' => array ('default' => 0),
            )
        ));
    }
    
    public function validate($entity, array $options = array()) {
        
        if ( !$entity->ctid )
            throw new \Natty\ORM\EntityException('Required index "ctid" has an invalid value.');
        
        if ( !$entity->module )
            throw new \Natty\ORM\EntityException('Required index "module" has an invalid value.');
        
        parent::validate($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        parent::onSave($entity, $options);
        \Module\System\Controller::rebuildRoutes();
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        parent::onDelete($entity, $options);
        \Module\System\Controller::rebuildRoutes();
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        
        $output = array ();
        
        if ( $user->can('cms--manage contenttype') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/cms/content-types/' . $entity->ctid) . '">Edit</a>';
        
            $output['delete'] = '<a href="' . \Natty::url('backend/cms/content-types/' . $entity->ctid . '/action', array (
                'do' => 'delete',
                'with' => $entity->ctid,
            )) . '" data-ui-init="confirmation">Delete</a>';
        
            $output['attribute'] = '<a href="' . \Natty::url('backend/cms/content-types/' . $entity->ctid . '/attribute') . '">Manage Attributes</a>';
            $output['display'] = '<a href="' . \Natty::url('backend/cms/content-types/' . $entity->ctid . '/display') . '">Manage Display</a>';
            
        endif;
        
        return parent::buildBackendLinks($entity, $options) + $output;
        
    }
    
}