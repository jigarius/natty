<?php

namespace Module\Eav\Classes;

class AttributeHandler 
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    protected static $crud_helpers = array ();
    
    public function __construct() {
        parent::__construct(array (
            'etid' => 'eav--attribute',
            'tableName' => '%__eav_attribute',
            'modelName' => array ('attribute', 'attributes'),
            'keys' => array (
                'id' => 'aid',
                'code' => 'acode',
                'label' => 'acode',
            ),
            'entityObjectClass' => 'Module\\Eav\\Classes\\AttributeObject',
            'properties' => array (
                'aid' => array ('required' => 1),
                'acode' => array ('required' => 1),
                'dtid' => array ('required' => 1),
                'module' => array ('required' => 1),
                'name' => array ('required' => 1),
                'description' => array ('default' => NULL),
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
                            'label' => 'hidden',
                        )
                    ),
                    'storage' => array (),
                ), 'sdata' => TRUE),
                'isConfigured' => array ('default' => 0),
                'isLocked' => array ('default' => 0),
                'status' => array ('default' => 1),
            )
        ));
    }
    
    protected function onBeforeSave(&$entity, array $options = array ()) {
        
        // Call the datatype handler
        $crud_helper = self::getCrudHelper($entity->dtid);
        $crud_helper::onBeforeAttributeSave($entity);
        
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
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onSave(&$entity, array $options = array()) {
        
        $crud_helper = self::getCrudHelper($entity->dtid);
        $crud_helper::onAttributeSave($entity);
        
        parent::onSave($entity, $options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array()) {
        
        $crud_helper = self::getCrudHelper($entity->dtid);
        $crud_helper::onBeforeAttributeDelete($entity);
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    protected function onDelete(&$entity, array $options = array()) {
        
        $crud_helper = self::getCrudHelper($entity->dtid);
        $crud_helper::onAttributeDelete($entity);
        
        parent::onDelete($entity, $options);
        
    }
    
    public static function getCrudHelper($dtid) {
        if ( !isset (self::$crud_helpers[$dtid]) ):
            $dtid_parts = explode('--', $dtid);
            $crud_helper = '\\Module\\' . ucfirst($dtid_parts[0]) . '\\Classes\\Eav\\Datatype_' . ucfirst($dtid_parts[1]) . 'Helper';
            self::$crud_helpers[$dtid] = $crud_helper;
        endif;
        return self::$crud_helpers[$dtid];
    }
    
    public static function readAttributeInstances($etid, $egid = '*', array $options = array ()) {
        
        static $cache;
        if ( !is_array ($cache) )
            $cache = array ();
        
        $cache_key = $etid . ':' . $egid;
        
        if ( !isset ($cache[$cache_key]) ):
            
            $attrinst_handler = \Natty::getHandler('eav--attrinst');
            $attrinsts = $attrinst_handler->read(array (
                'key' => array (
                    'etid' => $etid,
                    'status' => 1,
                ),
                'ordering' => array (
                    'ooa' => 'asc',
                )
            ));
            
            // Arrange attributes by acode
            $cache[$etid . ':*'] = array ();
            $cache[$cache_key] = array ();
            foreach ( $attrinsts as $attrinst ):
                if ( !isset ($cache[$attrinst->etid . ':' . $attrinst->egid]) )
                    $cache[$etid . ':' . $attrinst->egid] = array ();
                $cache[$etid . ':*'][$attrinst->acode] = $attrinst;
                $cache[$etid . ':' . $attrinst->egid][$attrinst->acode] = $attrinst;
            endforeach;
            
        endif;
        
        $output = $cache[$cache_key];
        
        // Order items as per view mode?
        if ( isset ($options['viewMode']) ):
            $view_mode = $options['viewMode'];
            foreach ( $output as $aid => &$o_attr ):
                if ( !isset($o_attr->settings['output'][$view_mode]) )
                    $o_attr->settings['output'][$view_mode] = array (
                        'method' => '',
                        'label' => 'hidden',
                        'ooa' => '',
                    );
                unset ($o_attr);
            endforeach;
            uasort($output, 'natty_compare_ooa');
        endif;
        
        return $output;
        
    }
    
    public static function entityFormHandle(array &$data) {
        
        $etid = $data['etid'];
        $egid = $data['egid'];
        $ail = isset ($data['ail'])
                ? $data['ail'] : \Natty::getOutputLangId();
        $form =& $data['form'];
        $entity =& $data['entity'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                $form->items['eav'] = array (
                    '_widget' => 'container',
                    '_label' => 'Attributes',
                    '_data' => array (),
                );

                $attrinsts = self::readAttributeInstances($etid, $egid);
                foreach ( $attrinsts as $attrinst ):
                    $crud_helper = $attrinst->crudHelper;
                    $crud_helper::attachInputForm($attrinst, $form, $entity);
                endforeach;
                
                if ( 0 === sizeof($form->items['eav']['_data']) )
                    $form->items['eav']['_display'] = 0;
                
                break;
        endswitch;
        
    }
    
    public static function attachEntitySave($etid, $egid, $entity, &$form, array $options = array ()) {
        
        $attrinsts = self::readAttributeInstances($etid, $egid);
        
        foreach ( $attrinsts as $attrinst ):
            $crud_helper = self::getCrudHelper($attrinst->dtid);
            $crud_helper::saveInstanceValues($attrinst, $entity);
        endforeach;
        
    }
    
    public static function attachEntityRead($etid, array &$entities, array $options = array ()) {
        
        $attrinst_coll = self::readAttributeInstances($etid, '*');
        $eids = array_keys($entities);
        
        // Do nothing if there are no attributes
        if ( 0 === sizeof($attrinst_coll) )
            return;
        
        // Get attribute value read query
        $query_coll = self::buildValueReadQuery($etid, $options);
        
        // Add required filters to these queries
        foreach ($query_coll as &$query):
            
            // Specify Entity Object IDs
            $query .= ' WHERE {data}.{eid} IN ("' . implode('", "', $eids) . '")';
            
            // Specify language, if specified
            if ( isset ($options['language']) ):
                $query .= ' AND {data}.{ail} = "' . \Natty::getOutputLangId() . '"';
            endif;
            
            unset ($query);
            
        endforeach;
        
        // Combine the queries and retrieve the data
        $query = implode(' UNION ', $query_coll);
        $value_records = \Natty::getDbo()->query($query)->execute();
        
        while ( $value_record = $value_records->fetch() ):
            $eid = $value_record['eid'];
            $acode = $value_record['acode'];
            $attrinst = $attrinst_coll[$acode];
            $crud_helper = $attrinst->crudHelper;
            $crud_helper::attachInstanceValues($attrinst, $entities[$eid], $value_record);
        endwhile;
        
    }
    
    public static function attachEntityView($etid, $egid, &$entity, array $options = array ()) {
        
        $view_mode = $options['viewMode'];
        
        $attrinsts = self::readAttributeInstances($etid, $egid, $options);
        foreach ( $attrinsts as $attrinst ):
            $crud_helper = self::getCrudHelper($attrinst->dtid);
            $attrinst_rarray = $crud_helper::renderInstanceValues($attrinst, $entity, $view_mode);
            if ( $attrinst_rarray ):
                $attrinst_rarray['_ooa'] = $attrinst->settings['output'][$view_mode]['ooa'];
                $entity->build[$attrinst->acode] = $attrinst_rarray;
            endif;
        endforeach;
        
    }
    
    public static function attachEntityDelete($etid, $egid, &$entity, array $options = array () ) {
        
        $attrinsts = self::readAttributeInstances($etid, $egid);
        
        foreach ( $attrinsts as $attrinst ):
            $crud_helper = self::getCrudHelper($attrinst->dtid);
            $crud_helper::deleteInstanceValues($attrinst, $entity);
        endforeach;
        
    }
    
    /**
     * Builds and returns a query for reading all instance values for all 
     * Entity Objects of a given Entity Type. Values returned can be affected
     * by the $options array which may include the following options:<br />
     * attributes: Specifying which attributes are to be read;<br />
     * @return array An array of queries for reading attribute values, usually
     * executed together in UNION after adding desired filters.
     */
    public static function buildValueReadQuery($etid, array $options = array ()) {
        
        static $cache;
        if ( !is_array ($cache) )
            $cache = array ();
        
        if ( !isset ($cache[$etid]) ):
            
            $attrinsts = self::readAttributeInstances($etid, '*');
            $queries = array ();

            // Determine data-type specific columns
            $special_columns = array ();
            foreach ( $attrinsts as $attrinst ):

                $attrinst_tablename = $attrinst->settings['storage']['tablename'];
                $attrinst_columns = $attrinst->settings['storage']['columns'];
                $queries[$attrinst->acode] = array (
                    'tablename' => $attrinst_tablename,
                    'alias' => $attrinst->acode,
                    'columns' => $attrinst_columns,
                );
                $special_columns = array_merge($special_columns, $attrinst_columns);

                unset ($attrinst, $attrinst_tablename, $attrinst_columns);

            endforeach;
            $special_columns = array_unique($special_columns);

            // Build a merged query - missing columns would be read as NULL
            foreach ( $queries as &$definition ):

                $query = 'SELECT'
                        . ' "' . $definition['alias'] . '" {acode},'
                        . ' {data}.{aiid},'
                        . ' {data}.{eid},'
                        . ' {data}.{ail},'
                        . ' {data}.{vno}';
                foreach ( $special_columns as $column_key => $column ):
                    $query .= ',';
                    if ( in_array($column, $definition['columns']) ) {
                        $query .= ' {data}.{' . $column . '}';
                    }
                    else {
                        $query .= ' NULL {' . $column . '}';
                    }
                endforeach;
                $query .= ' FROM {' . $definition['tablename'] . '} {data}';

                $definition = $query;
                unset ($definition);

            endforeach;
            
            $cache[$etid] = $queries;
            
        endif;
        
        return $cache[$etid];
        
    }
    
}