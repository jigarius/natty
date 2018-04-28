<?php

namespace Module\Catalog\Classes;

class ProducttypeHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'catalog--producttype',
            'tableName' => '%__catalog_producttype',
            'modelName' => array ('product type', 'product types'),
            'keys' => array (
                'id' => 'ptid',
            ),
            'properties' => array (
                'ptid' => array (),
                'name' => array ('isTranslatable' => 1),
                'status' => array (),
                'isLocked' => array ('default' => 0),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        
        $output = array ();
        if ( $user->can('catalog--manage producttype entities') ):
            
            $output['edit'] = '<a href="' . \Natty::url('backend/product-types/' . $entity->ptid) . '">Edit</a>';
        
            $output['attributes'] = '<a href="' . \Natty::url('backend/catalog/product-types/' . $entity->ptid . '/attribute') . '">Manage Attributes</a>';
            $output['display'] = '<a href="' . \Natty::url('backend/catalog/product-types/' . $entity->ptid . '/display') . '">Manage Display</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
}