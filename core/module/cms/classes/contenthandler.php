<?php

namespace Module\Cms\Classes;

use \Module\Eav\Classes\AttributeHandler;

class ContentHandler 
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct() {
        parent::__construct(array (
            'tableName' => '%__cms_content',
            'etid' => 'cms--content',
            'keys' => array (
                'id' => 'cid',
                'label' => 'name',
                'group' => 'ctid'
            ),
            'modelName' => array ('content', 'content'),
            'properties' => array (
                'cid' => array (),
                'name' => array ('isTranslatable' => TRUE),
                'ctid' => array (),
                'idCreator' => array (),
                'dtCreated' => array ('default' => NULL),
                'dtModified' => array ('default' => NULL),
                'dtPublished' => array ('default' => NULL),
                'isPromoted' => array ('default' => 0),
                'status' => array ('default' => 1)
            )
        ));
    }
    
    public function create(array $data = array()) {
        if ( !isset ($data['idCreator']) ):
            $user = \Natty::getUser();
            $data['idCreator'] = $user->uid;
        endif;
        if ( !isset ($data['dtCreated']) ):
            $data['dtCreated'] = date('Y-m-d H:i:s');
        endif;
        return parent::create($data);
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        if ( !$entity->isNew )
            $entity->dtModified = date('Y-m-d H:i:s');
        if ( $entity->status > 0 && !$entity->dtPublished )
            $entity->dtPublished = $entity->dtModified;
        parent::onBeforeSave($entity, $options);
    }
    
    protected function onRead(array &$entities, array $options = array ()) {
        AttributeHandler::attachEntityRead($this->etid, $entities, $options);
        parent::onRead($entities, $options);
    }
    
    protected function onSave(&$entity, array $options = array()) {
        AttributeHandler::attachEntitySave($this->etid, $entity->ctid, $entity, $options);
        parent::onSave($entity, $options);
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        AttributeHandler::attachEntityDelete($this->etid, $entity->ctid, $entity, $options);
        parent::onDelete($entity, $options);
    }
    
    public function getEntityGroupData( $rebuild = FALSE ) {
        
        static $cache;
        
        if ( !is_array($cache) || $rebuild ):
            
            $cache = array ();
            
            $ctype_coll = \Natty::getHandler('cms--contenttype')
                    ->read();
            foreach ( $ctype_coll as $ctype ):
                $cache[$ctype->ctid] = array (
                    'id' => $ctype->ctid,
                    'name' => $ctype->name,
                    'uri' => 'backend/cms/content-types/' . $ctype->ctid,
                );
            endforeach;
        
        endif;
        
        return $cache;
        
    }
    
    public function allowAction($entity, $action, $user = NULL) {
        
        $user = \Natty::getUser();
        $output = TRUE;
        
        switch ( $action ):
            case 'view':
                if ( !$entity->status ):
                    if ( $entity->idCreator != $user->uid && !$user->can('cms--view unpublished content') )
                        $output = FALSE;
                endif;
                if ( !$user->can('cms--view published content') )
                    $output = FALSE;
                break;
            default:
                $output = FALSE;
                break;
        endswitch;
        
        return $output;
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $output = array ();
        
        if ( $this->allowAction($entity, 'view') ):
            $output['view'] = array (
                '_render' => 'element',
                '_element' => 'a',
                '_data' => 'View',
                'href' => $entity->call('getUri'),
                'target' => '_blank',
            );
            $output['edit'] = array (
                '_render' => 'element',
                '_element' => 'a',
                '_data' => 'Edit',
                'href' => \Natty::url('backend/cms/content/' . $entity->cid),
            );
            $output['delete'] = '<a href="' . \Natty::url('backend/cms/content/action', array (
                'do' => 'delete',
                'with' => $entity->cid,
            )) . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        if ( $overrides = parent::buildFrontendLinks($entity, $options) )
            $output = array_merge($output, $overrides);
        
        return $output;
        
    }
    
    public function buildFrontendLinks(&$entity, array $options = array()) {
        
        $output = array ();
        
        if ( $this->allowAction($entity, 'view') ):
            $output['view'] = array (
                '_render' => 'element',
                '_element' => 'a',
                '_data' => 'Read more',
                'href' => $entity->call('getUri'),
                'class' => array ('k-button'),
            );
        endif;
        
        if ( $overrides = parent::buildFrontendLinks($entity, $options) )
            $output = array_merge($output, $overrides);
        
        return $output;
        
    }
    
    public function buildContent(&$entity, $options = array()) {
        
        parent::buildContent($entity, $options);
        
        // Attach attributes
        AttributeHandler::attachEntityView($this->etid, $entity->ctid, $entity, $options);
        
    }
    
}