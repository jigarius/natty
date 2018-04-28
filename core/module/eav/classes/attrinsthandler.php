<?php

namespace Module\Eav\Classes;

class AttrinstHandler 
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct() {
        parent::__construct(array (
            'tableName' => '%__eav_attrinst',
            'etid' => 'eav--attrinst',
            'keys' => array (
                'id' => 'aiid',
                'label' => 'name',
            ),
            'singularName' => 'attribute',
            'pluralName' => 'attributes',
            'entityObjectClass' => 'Module\\Eav\\Classes\\AttrinstObject',
            'properties' => array (
                'aiid' => array (),
                'etid' => array (),
                'egid' => array (),
                'aid' => array (),
                'acode' => array (),
                'dtid' => array ('sdata' => TRUE),
                'crudHelper' => array ('sdata' => TRUE),
                'name' => array (),
                'description' => array ('default' => NULL),
                'ooa' => array (),
                'settings' => array ('default' => array (
                    'input' => array (
                        'method' => '',
                        'nov' => 1,
                        'required' => 0,
                    ),
                    'output' => array (
                        'default' => array (
                            'method' => '',
                            'ooa' => 0,
                            'label' => 'above',
                        ),
                    ),
                    'storage' => array (),
                ), 'sdata' => TRUE),
                'isLocked' => array ('default' => 0),
                'isTranslatable' => array ('default' => 0, 'sdata' => TRUE),
                'status' => array ('default' => 1),
            )
        ));
    }
    
    function __set($name, $value) {
        if ( 'settings' == $name )
            return $this->settings = natty_array_merge_nested($this->$name, $value);
        return parent::__set($name, $value);
    }
    
    public function create(array $data = array()) {
        
        // Must specify an Attribute ID
        if ( !isset ($data['aid']) )
            throw new \LogicException('Missing expected value index "aid"');
        
        return parent::create($data);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        
        if ( $entity->isNew ):
            
            $attribute = \Natty::getEntity('eav--attribute', $entity->aid);
            $entity->acode = $attribute->acode;
            $entity->dtid = $attribute->dtid;
            $entity->crudHelper = \Module\Eav\Classes\AttributeHandler::getCrudHelper($attribute->dtid);
            $entity->settings = natty_array_merge_nested($attribute->settings, $entity->settings);
            
            $conflict = $this->readByKeys(array (
                'aid' => $entity->aid,
                'etid' => $entity->etid,
                'egid' => $entity->egid,
                'status' => 1,
            ), array (
                'nocache' => TRUE,
            ));
            if ( sizeof($conflict) )
                throw new \Natty\ORM\EntityException('Conflicting entity already exists.');
            
        endif;
        
        $crud_helper = $entity->crudHelper;
        $crud_helper::onBeforeInstanceSave($entity);
        
        // Assign input method defaults
        if ( $entity->settings['input']['method'] ):
            $imethod_coll = \Module\Eav\Controller::readInputMethods();
            $imid = $entity->settings['input']['method'];
            if ( !isset ($imethod_coll[$imid]) ) {
                $entity->settings['input']['method'] = '';
            }
            else {
                $imethod = $imethod_coll[$imid];
                $default_settings = $imethod['helper']::getDefaultSettings();
                $entity->settings['input'] = array_merge($default_settings, $entity->settings['input']);
            }
        endif;
        
        // Assign output method defaults
        if ( $entity->settings['input']['method'] ):
            $omethod_coll = \Module\Eav\Controller::readOutputMethods();
            foreach ( $entity->settings['output'] as $vmid => $output_settings ):
                $omid = $output_settings['method'];
                if ( !isset ($omethod_coll[$omid]) ) {
                    $output_settings['method'] = '';
                    $output_settings['label'] = 'hidden';
                }
                else {
                    $omethod = $omethod_coll[$omid];
                    $default_settings = $omethod['helper']::getDefaultSettings();
                    $output_settings = array_merge($default_settings, $output_settings);
                }
                $entity->settings['output'][$vmid] = $output_settings;
            endforeach;
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        
        // Attribute instance was deleted
        if ( intval($entity->status) < 0 ):
            // If the attribute is no-longer in use, then mark it for deletion.
            // Only mark for deletion if the attribute is not locked.
            $in_use = $this->getDbo()
                    ->getQuery('select', $this->tableName . ' ai')
                    ->addExpression('1 {conflict}')
                    ->addSimpleCondition('aid', ':aid')
                    ->addSimpleCondition('status', ':status', '!=')
                    ->limit(1)
                    ->execute(array (
                        'aid' => $entity->aid,
                        'status' => -1,
                    ))
                    ->fetchColumn();
            if ( !$in_use ):
                $attribute = \Natty::getEntity('eav--attribute', $entity->aid);
                if ( !$attribute->isLocked ):
                    $attribute->status = -1;
                    $attribute->save();
                endif;
            endif;
        endif;
        
        $crud_helper = $entity->crudHelper;
        $crud_helper::onInstanceSave($entity);
        
        parent::onSave($entity, $options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array ()) {
        
        $crud_helper = $entity->crudHelper;
        $crud_helper::onBeforeInstanceDelete($entity);
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        
        $crud_helper = $entity->crudHelper;
        $crud_helper::onInstanceDelete($entity);
        
        parent::onDelete($entity, $options);
        
    }
    
}