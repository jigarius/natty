<?php

namespace Module\Payrec\Classes;

class MethodHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'payrec--method',
            'tableName' => '%__payrec_method',
            'modelName' => array ('method', 'methods'),
            'keys' => array (
                'id' => 'mid',
            ),
            'properties' => array (
                'mid' => array (),
                'module' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'type' => array (
                    'required' => 1,
                ),
                'name' => array (
                    'isTranslatable' => 1,
                ),
                'description' => array (
                    'isTranslatable' => 1,
                    'default' => NULL,
                ),
                'helper' => array (
                    'sdata' => 1,
                ),
                'settings' => array (
                    'sdata' => 1,
                    'default' => array (),
                ),
                'status' => array (
                    'default' => 0,
                ),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function rebuild() {
        
        // Read existing data
        $old_coll = $this->read(array (
            'conditions' => array (
                array ('AND', '1=1')
            ),
        ));
        
        // Trigger an event to collect data
        $new_coll = array ();
        \Natty::trigger('payrec--methodDeclare', $new_coll);
        \Natty::trigger('payrec--methodRevise', $new_coll);
        
        foreach ($new_coll as $mid => &$record):
            
            $record['mid'] = $mid;
            if (isset ($old_coll[$mid])) {
                
                $method = $old_coll[$mid];
                if (isset ($record['settings'])):
                    $method->settings = array_merge($method->settings, $record['settings']);
                    unset ($record['settings']);
                endif;
                $method->setState($record);
                
                $new_coll[$mid] = $method;
                unset ($old_coll[$mid]);
                
            }
            else {
                $record['isNew'] = TRUE;
                $method = $this->create($record);
            }
        
            $mid_parts = explode('-', $mid, 2);
            $method->helper = '\\Module\\' . ucfirst($method->module) . '\\Classes\\Payrec\\' . ucfirst($mid_parts[0]) . 'Method_' . ucfirst($mid_parts[1]) . 'Helper';
            $method->save();
            
            unset ($record);
            
        endforeach;
        
        // Delete removed methods
        foreach ($old_coll as $method):
            $method->delete();
        endforeach;
        
        return $new_coll;
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $output = array ();
        $auth_user = \Natty::getUser();
        
        if ($auth_user->can('payrec--manage method entities'))
            $output['configure'] = '<a href="' . \Natty::url('backend/payrec/methods/' . $entity->mid) . '">Configure</a>';
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    /**
     * Orchestrates a payment or receipt transaction
     * @param \Natty\ORM\EntityObject $entity
     * @param array $options An associative array of transaction information.
     */
    public function doTransaction($entity, array $options) {
        
        // Merge with defaults
        $options = array_merge(array (
            'mid' => $entity->mid,
            'contextType' => NULL,
            'contextId' => NULL,
            'contextUrl' => NULL,
            'contextLabel' => NULL,
            'description' => '',
            'idCurrency' => NULL,
            'xRate' => NULL,
            'variables' => array (
                'default' => array (),
            ),
            'amount' => array ('default' => 0),
        ), $options);
        
        $tran_handler = \Natty::getHandler('payrec--tran');
        $tran = $tran_handler->create($options);
        
        // Call the method orchestrator
        $helper = $entity->helper;
        $helper::doTransaction($tran, $entity);
        
    }
    
}