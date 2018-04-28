<?php

namespace Module\Cms\Classes;

class MenuHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'etid' => 'cms--menu',
            'tableName' => '%__cms_menu',
            'singularName' => 'menu',
            'pluralName' => 'menus',
            'keys' => array (
                'id' => 'mid',
                'label' => 'name'
            ),
            'properties' => array (
                'mid' => array (),
                'mcode' => array (),
                'name' => array ('isTranslatable' => 1),
                'isLocked' => array ('default' => 0),
            )
        );
        
        parent::__construct($options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array()) {
        
        // Delete menu items
        $menuitem_handler = \Natty::getHandler('cms--menuitem');
        $menuitem_coll = $menuitem_handler->read(array (
            'key' => array ('mid' => $entity->mid),
            'ordering' => array ('ooa' => 'desc'),
        ));
        foreach ( $menuitem_coll as $menuitem ):
            $menuitem->delete();
        endforeach;
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('cms--manage menu entities') ):
            $output['items'] = '<a href="' . \Natty::url('backend/cms/menu/' . $entity->mid . '/items') . '">Manage Items</a>';
            $output['edit'] = '<a href="' . \Natty::url('backend/cms/menu/' . $entity->mid . '/edit') . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/cms/menu/action', array (
                'do' => 'delete',
                'with' => $entity->mid,
            )) . '" target="_blank" data-ui-init="confirmation">Delete</a>';
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    public static function buildMenuTree(array $menuitem_coll, array $options = array ()) {
        
        $options = array_merge(array (
            'parentId' => 0,
            'level' => 0,
            'levels' => NATTY_MAX_LEVELS,
        ), $options);
        
        $output = '';
        
        foreach ( $menuitem_coll as $menuitem ):
            
            $menuitem->parentId = (int) $menuitem->parentId;
            
            // Ignore links which are not in scope
            if ( $menuitem->parentId != $options['parentId'] )
                continue;
            
            // Render current link
            $menuitem_href = natty_is_abspath($menuitem->href)
                    ? $menuitem->href : \Natty::url($menuitem->href);
            $menuitem_classname = 'menuitem';
            $menuitem_markup = '<a href="' . $menuitem_href . '" class="menuitem-' . $menuitem->miid . '">' . $menuitem->name . '</a>';
            
            // Render children
            $children_markup = self::buildMenuTree($menuitem_coll, array (
                'parentId' => $menuitem->miid,
                'level' => $options['level']+1,
            ));
            
            if ( $children_markup )
                $menuitem_classname .= ' expandable';
            
            
            $output .= '<li class="' . $menuitem_classname . '">'
                        . $menuitem_markup
                        . $children_markup
                    . '</li>';
            
        endforeach;
        
        // Wrap it in a list object
        if ( strlen($output) > 0 ):
            $output = '<ul class="n-menu level-' . $options['level'] . '">' . $output . '</ul>';
        endif;
        
        return $output;
        
    }
    
}