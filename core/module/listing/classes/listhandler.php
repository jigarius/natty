<?php

namespace Module\Listing\Classes;

class ListHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array_merge(array (
            'etid' => 'listing--list',
            'tableName' => '%__listing_list',
            'modelName' => array ('list', 'lists'),
            'entityObjectClass' => '\\Module\\Listing\\Classes\\ListObject',
            'keys' => array (
                'id' => 'lid',
                'code' => 'lcode',
            ),
            'properties' => array (
                'lid' => array (),
                'lcode' => array (),
                'name' => array ('isTranslatable' => 1),
                'description' => array ('isTranslatable' => 1),
                'settings' => array (
                    'sdata' => 1,
                    'default' => array (
                        'etid' => NULL,
                    ),
                ),
                'visibility' => array (
                    'sdata' => 1,
                    'default' => array (),
                ),
                'status' => array ('default' => 1),
            ),
        ), $options);
        
        parent::__construct($options);
        
    }
    
    public function readById($identifier, array $options = array()) {
        
        if ( !is_numeric($identifier) ) {
            return $this->read(array (
                'key' => array ('lcode' => $identifier),
                'unique' => 1,
            ));
        }
        else {
            return parent::readById($identifier, $options);
        }
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        // Add default display
        if ( !isset ($entity->visibility['default']) ):
            $entity->visibility['default'] = array (
                'id' => 'default',
                'name' => 'Default',
                'isLocked' => 1,
                'status' => 1,
            );
        endif;
        
        // Prepare defaults
        $display_defaults = $entity->createVisibility();
        $filter_defaults = array (
            'name' => NULL,
            'description' => NULL,
            'nature' => NULL,
            'code' => NULL,
            'tableName' => NULL,
            'tableAlias' => NULL,
            'columnName' => NULL,
            'dtid' => NULL,
            'method' => '=',
            'operand' => NULL,
            'i18n' => NULL,
        );
        $sort_defaults = array (
            'name' => NULL,
            'description' => NULL,
            'nature' => NULL,
            'code' => NULL,
            'tableName' => NULL,
            'tableAlias' => NULL,
            'columnName' => NULL,
            'dtid' => NULL,
            'method' => NULL,
            'i18n' => NULL,
        );
        
        // Merge all display data with defaults
        foreach ( $entity->visibility as $did => &$display ):
            
            $display['id'] = $did;
            $display = natty_array_merge_nested($display_defaults, $display);
            
            // Validate filter rules
            foreach ( $display['filterData'] as $id => &$item_defi ):
                $item_defi['id'] = $id;
                $item_defi = array_merge($filter_defaults, $item_defi);
                
                if ( !$item_defi['name'] )
                    $item_defi['name'] = $item_defi['code'];
                
                unset ($item_defi);
            endforeach;
            
            // Validate sort rules
            foreach ( $display['sortData'] as $id => &$item_defi ):
                $item_defi['id'] = $id;
                $item_defi = array_merge($sort_defaults, $item_defi);
                
                if ( !$item_defi['name'] )
                    $item_defi['name'] = $item_defi['code'];
                
                unset ($item_defi);
            endforeach;
            
            unset ($display);
        
        endforeach;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        
        parent::onSave($entity, $options);
        
        // Rebuild routes
        $mod_system = \Natty::getPackage('module', 'system');
        $mod_system::rebuildRoutes();
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('listing--administer') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/listing/' . $entity->lid) . '">Edit</a>';
            $output['visibility'] = '<a href="' . \Natty::url('backend/listing/' . $entity->lid . '/visibility') . '">Visibility</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}
