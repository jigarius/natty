<?php

namespace Module\Catalog\Classes;

use Module\Eav\Classes\AttributeHandler;

class ProductHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'catalog--product',
            'tableName' => '%__catalog_product',
            'modelName' => array ('product', 'products'),
            'keys' => array (
                'id' => 'pid',
            ),
            'properties' => array (
                'pid' => array (),
                'pcode' => array ('default' => NULL),
                'ptid' => array (),
                'cid' => array (),
                'name' => array ('isTranslatable' => 1),
                'costPrice' => array ('default' => NULL),
                'salePrice' => array ('default' => NULL),
                'trid' => array (),
                'length' => array ('default' => NULL),
                'breadth' => array ('default' => NULL),
                'height' => array ('default' => NULL),
                'weight' => array ('default' => NULL),
                'shippingCharge' => array ('default' => NULL),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        AttributeHandler::attachEntitySave($this->etid, $entity->ptid, $entity, $options);
        parent::onSave($entity, $options);
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        AttributeHandler::attachEntityDelete($this->etid, $entity->ptid, $entity, $options);
        parent::onDelete($entity, $options);
    }
    
    protected function onRead(array &$entities, array $options = array ()) {
        AttributeHandler::attachEntityRead($this->etid, $entities, $options);
        parent::onRead($entities, $options);
    }
    
    public function getEntityGroupData($rebuild = FALSE) {
        
        static $cache;
        
        if ( !is_array($cache) ):
            
            $ptype_coll = \Natty::getHandler('catalog--producttype')
                ->read();
            foreach ( $ptype_coll as $ptid => $ptype ):
                $cache[$ptid] = array (
                    'id' => $ptype->ptid,
                    'name' => $ptype->name,
                    'uri' => 'backend/catalog/product-types/' . $ptype->ptid,
                );
            endforeach;
            
        endif;
        
        return $cache;
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('catalog--manage product entities') ):
            
            $output['view'] = '<a href="' . \Natty::url('catalog/product/' . $entity->pid) . '" target="_blank">View</a>';
            $output['edit'] = '<a href="' . \Natty::url('backend/catalog/products/' . $entity->pid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/catalog/products/' . $entity->pid . '/action', array (
                'do' => 'delete',
                'with' => $entity->pid,
            )) . '" data-ui-init="confirmation">Delete</a>';
            
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    public function buildContent(&$entity, $options = array()) {
        
        parent::buildContent($entity, $options);
        
        // Attach attributes
        AttributeHandler::attachEntityView($this->etid, $entity->ptid, $entity, $options);
        
    }
    
    public function getEntityCategoryIds(&$entity, $rebuild = FALSE) {
        
        if ( !isset ($entity->categoryIds) || $rebuild ):
            
            $tablename = '%__catalog_product_category_map';
            $entity->categoryIds = $this->getDbo()->read($tablename, array (
                'columns' => array ('cid'),
                'key' => array (
                    'pid' => $entity->pid,
                ),
                'fetch' => array (\PDO::FETCH_COLUMN),
            ));
        endif;
        
        return $entity->categoryIds;
        
    }
    
    public function setEntityCategoryIds(&$entity, $ids = NULL) {
        
        // If the entity is not saved, do nothing
        if ( !$entity->pid )
            return;
        
        $dbo = $this->getDbo();
        $tablename = '%__catalog_product_category_map';
        
        $dbo->delete($tablename, array (
            'key' => array ('pid' => $entity->pid),
        ));
        
        if ( sizeof($ids) ):
            $stmt = $dbo->getQuery('insert', $tablename)
                    ->addColumns(array ('pid', 'cid'))
                    ->prepare();
            foreach ( $ids as $id ):
                $stmt->execute(array (
                    'pid' => $entity->pid,
                    'cid' => $id,
                ));
            endforeach;
        endif;
        
        $entity->categoryIds = $ids;
        
    }
    
}