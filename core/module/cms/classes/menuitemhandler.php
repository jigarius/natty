<?php

namespace Module\Cms\Classes;

class MenuitemHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'cms--menuitem',
            'tableName' => '%__cms_menuitem',
            'singularName' => 'menu item',
            'pluralName' => 'menu items',
            'keys' => array (
                'id' => 'miid',
                'label' => 'name',
                'level' => 'level',
                'parent' => 'parentId',
                'ooa' => 'ooa',
            ),
            'properties' => array (
                'miid' => array (),
                'mid' => array (),
                'mcode' => array (),
                'parentId' => array ('default' => 0),
                'level' => array ('default' => 0),
                'name' => array ('isTranslatable' => 1),
                'markup' => array ('default' => NULL, 'isTranslatable' => 1),
                'href' => array ('default' => NULL),
                'ooa' => array (),
                'isLocked' => array ('default' => 0),
                'status' => array ('default' => 1, 'isTranslatable' => 1),
            )
        );
        
        parent::__construct($options);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        
        // Set menu code as per menu ID
        if ( !$entity->mcode ):
            $menu = \Natty::getEntity('cms--menu', $entity->mid);
            $entity->mcode = $menu->mcode;
        endif;
        
        // Set level as per parent
        if ( $entity->parentId > 0 ):
            $parent = $this->readById($entity->parentId);
            $entity->level = $parent->level+1;
        endif;
        
        // Set order of appearance
        if ( $entity->isNew && !$entity->ooa ):
            $order = \Natty::getDbo()
                ->getQuery('select', '%__cms_menuitem')
                ->addColumn('ooa')
                ->addComplexCondition('AND', array ('mid', '=', ':mid'))
                ->addComplexCondition('AND', array ('parentId', '=', ':parentId'))
                ->limit(1)
                ->execute(array ('mid' => $entity->mid, 'parentId' => $entity->parentId))
                ->fetchColumn();
            $entity->ooa = $order ? $order + 5 : 5;
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
}