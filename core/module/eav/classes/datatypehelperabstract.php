<?php

namespace Module\Eav\Classes;

use Natty\Form\FormObject;

/**
 * Data-Type CRUD Helper: Contains methods for handling attribute interactions
 * with the storage layer
 */
abstract class DatatypeHelperAbstract 
extends \Natty\Uninstantiable {
    
    /**
     * Data-Type ID in the format module/datatype
     * @var string
     */
    protected static $dtid = FALSE;
    
    final protected static function getDtid() {
        if ( !static::$dtid )
            trigger_error(__CLASS__ . '::$dtid must be over-ridden by child class!', E_USER_ERROR);
        return static::$dtid;
    }
    
    /**
     * Returns default settings for this attribute
     * @reutrn array An array of default settings for an attribute or instance
     */
    public static function getDefaultSettings() {
        return array (
            'storage' => array (
                'tablename' => NULL,
                'columns' => array (),
            ),
            'input' => array (),
            'output' => array (
                'default' => array (),
            ),
        );
    }
    
    /**
     * Returns the tablename where deleted data for a given attribute is moved.
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     * @return string Tablename
     */
    public static function getUnpluggedTablename(AttrinstObject $attrinst) {
        return '%__eav_xdata_attrinst_' . $attrinst->aiid;
    }
    
    /**
     * Called before an attribute has been saved.
     * @param \Module\Eav\Classes\AttributeObject $attribute
     */
    public static function onBeforeAttributeSave(AttributeObject &$attribute) {
        
        // Merge with default settings
        $default_settings = static::getDefaultSettings();
        $attribute->settings = natty_array_merge_nested($default_settings, $attribute->settings);
        
    }
    
    /**
     * Called after an attribute has been saved.
     * @param \Module\Eav\Classes\AttributeObject $attribute
     */
    public static function onAttributeSave(AttributeObject &$attribute) {}
    
    /**
     * Called before an attribute has been deleted.
     * @param \Module\Eav\Classes\AttributeObject $attribute
     */
    public static function onBeforeAttributeDelete(AttributeObject &$attribute) {}
    
    /**
     * Called after an attribute has been deleted.
     * @param \Module\Eav\Classes\AttributeObject $attribute
     */
    public static function onAttributeDelete(AttributeObject &$attribute) {}
    
    /**
     * Called before an attribute instance is saved.
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     */
    public static function onBeforeInstanceSave(AttrinstObject &$attrinst) {
        
        // Merge with default settings
        $default_settings = self::getDefaultSettings();
        $attrinst->settings = natty_array_merge_nested($default_settings, $attrinst->settings);
        
        if ( $attrinst->isNew ):
            
            // Determine storage table
            $attrinst->settings['storage']['tablename'] = '%__eav_data_' . str_replace('--', '_', $attrinst->etid) . '_' . $attrinst->acode;
            $attrinst->settings['storage']['tablename'] = strtolower($attrinst->settings['storage']['tablename']);
            
            // Determine default storage columns
            $table_default = DatatypeHelperAbstract::getStorageTableDefinition($attrinst);
            $table_default_columns = $table_default['columns'];
            unset ($table_default_columns['value']);
            $table_default_columns = array_keys($table_default_columns);
            
            // Determine customized storage columns
            $table_custom = static::getStorageTableDefinition($attrinst);
            $table_custom_columns = array_keys($table_custom['columns']);
            
            // Determine value storage columns
            $value_storage_columns = array_diff($table_custom_columns, $table_default_columns);
            $attrinst->settings['storage']['columns'] = array_values($value_storage_columns);
            
        endif;
        
    }
    
    /**
     * Called after an attribute instance has been saved.
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     */
    public static function onInstanceSave(AttrinstObject &$attrinst) {
        
        // Attribute is new?
        if ( $attrinst->isNew )
            static::prepareStorage($attrinst);
        
        // Attribute is deleted/hidden?
        if ( intval($attrinst->status) < 0 )
            static::unplugStorage($attrinst);
        
    }
    
    /**
     * Called before an attribute instance has been deleted.
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     */
    public static function onBeforeInstanceDelete(AttrinstObject &$attrinst) {
        self::destroyStorage($attrinst);
    }
    
    /**
     * Called after an attribute instance has been deleted.
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     */
    public static function onInstanceDelete(AttrinstObject &$attrinst) {}
    
    protected static function getStorageTableDefinition(AttrinstObject $attrinst) {
        
        $definition = array (
            'name' => $attrinst->settings['storage']['tablename'],
            'description' => 'Storage for Attribute ID ' . $attrinst->aid . '.',
            'columns' => array (
                'egid' => array (
                    'description' => 'Entity Group ID',
                    'type' => 'varchar',
                    'length' => 128
                ),
                'eid' => array (
                    'description' => 'Entity Object ID',
                    'type' => 'varchar',
                    'length' => 64,
                    'flags' => array ('unsigned')
                ),
                'aiid' => array (
                    'description' => 'Attribute Instance ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned')
                ),
                'ail' => array (
                    'description' => 'As in language. FK: system_language.lid',
                    'type' => 'varchar',
                    'length' => 8,
                    'default' => 'UNDF',
                    'flags' => array ()
                ),
                'vno' => array (
                    'description' => 'Value Number for multi-value attributes',
                    'type' => 'int',
                    'length' => 4
                ),
                'value' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('eid', 'ail', 'vno')
                )
            ),
        );
        
        return $definition;
        
    }
    
    /**
     * Prepares storage for an attribute instance
     * @param \Module\Eav\Classes\AttrinstObject $attrinst
     */
    protected static function prepareStorage(AttrinstObject $attrinst) {
        
        $definition = static::getStorageTableDefinition($attrinst);
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        // See if storage exists
        if ( !$schema_helper->readTable($definition['name']) )
            $schema_helper->createTable($definition);
        
    }
    
    /**
     * Prepares the storage table for an an attribute for deletion later on. As
     * such, this method merely frees the table name space so that a new
     * attribute with the same attribute code (acode) can be recreated before
     * the data of this attribute is permanently deleted by cron).
     */
    protected static function unplugStorage(AttrinstObject $attrinst) {
        
        $tablename_old = $attrinst->settings['storage']['tablename'];
        $tablename_new = static::getUnpluggedTablename($attrinst);
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        $schema_helper->renameTable($tablename_old, $tablename_new);
        
    }
    
    protected static function destroyStorage(AttrinstObject $attrinst) {
        
        $tablename = self::getUnpluggedTablename($attrinst);
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        $schema_helper->dropTable($tablename);
        
    }
    
    protected static function truncateStorage(AttrinstObject $attrinst) {}
    
    public static function storageInUse(AttrinstObject $attrinst) {
        
        $tablename = $attrinst->settings['storage']['tablename'];
        
        $output = \Natty::getDbo()
                ->getQuery('select', $tablename)
                ->addExpression('1 {foo}')
                ->limit(1)
                ->execute()
                ->fetchColumn();
        
        return (bool) $output;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        $form = $data['form'];
        $attribute = $data['attribute'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                $input_methods = \Module\Eav\Controller::readInputMethods();
                foreach ( $input_methods as $imid => $input_method ):
                    if ( !in_array(static::$dtid, $input_method['datatypes']) ) {
                        unset ($input_methods[$imid]);
                    }
                    else {
                        $input_methods[$imid] = $input_method['name'];
                    }
                endforeach;
                
                $form->items['storage'] = array (
                    '_widget' => 'container',
                    '_label' => 'Storage Settings',
                    '_display' => 0,
                    '_data' => array (),
                );
                
                $form->items['input'] = array (
                    '_widget' => 'container',
                    '_label' => 'Input Settings',
                    '_data' => array (),
                );
                $form->items['input']['_data']['settings.input.method'] = array (
                    '_widget' => 'dropdown',
                    '_label' => 'Input Widget',
                    '_options' => $input_methods,
                    '_description' => 'The form widget used for taking user input.',
                    '_default' => $attribute->settings['input']['method'],
                    'required' => 1,
                    'placeholder' => '',
                );
                $form->items['input']['_data']['settings.input.required'] = array (
                    '_widget' => 'options',
                    '_label' => 'Is Required?',
                    '_options' => array (
                        1 => 'Yes',
                        0 => 'No',
                    ),
                    '_description' => 'Whether entering a value for this attribute is compulsory.',
                    '_default' => $attribute->settings['input']['required'],
                    'class' => array ('options-inline'),
                );
                $form->items['input']['_data']['settings.input.nov'] = array (
                    '_widget' => 'input',
                    '_label' => 'Number of Values',
                    '_default' => $attribute->settings['input']['nov'],
                    '_description' => 'Enter 0 to have upto 999 values for this attribute.',
                    'type' => 'number',
                    'required' => 1,
                    'class' => array ('widget-small'),
                    'maxlength' => 3,
                );
                
            break;
        endswitch;
        
    }
    
    public static function attachOutputSettingsForm(AttrinstObject $attrinst, FormObject &$form, $view_mode = 'default') {
        
        $output_settings = $attrinst->settings['output']['default'];
        if ( isset ($attrinst->settings['output'][$view_mode]) )
            $output_settings = array_merge($output_settings, $attrinst->settings['output'][$view_mode]);
        
        // Determine selected output method
        $omethod_coll = \Module\Eav\Controller::readOutputMethods();
        $omid_selected = $output_settings['method'];
        if ( !isset ($omethod_coll[$omid_selected]) ):
            $form->items['output-' . $view_mode] = array (
                '_widget' => 'markup',
                '_markup' => 'Unrecognized output format. Please choose a valid output format.',
            );
            return;
        endif;
        
        $omethod_selected = $omethod_coll[$omid_selected];
        $form->items['output-' . $view_mode] = $omethod_selected['helper']::attachSettingsForm($attrinst, $view_mode);
        
    }
    
    final public static function attachInputForm(AttrinstObject $attrinst, FormObject &$form, $entity) {
        
        $acode = $attrinst->acode;
        
        $input_methods = \Module\Eav\Controller::readInputMethods();
        $imid = $attrinst->settings['input']['method'];
        
        if ( !$imid || !isset($input_methods[$imid]) ) {
            $form->items['eav']['_data'][$acode] = array (
                '_widget' => 'markup',
                '_label' => $attrinst->name,
                '_markup' => 'Input method not defined.'
            );
        }
        else {
            
            $input_method = $input_methods[$imid];
            
            $callback_classname = $input_method['helper'];
            $callback_method = 'attachValueForm';
            
            if ( !method_exists($callback_classname, $callback_method) ) {
                $form->items['eav']['_data'][$acode] = array (
                    '_widget' => 'markup',
                    '_label' => $attrinst->name,
                    '_markup' => 'Could not attach input method.'
                );
            }
            else {
                
//                $callback_data = array (
//                    'attrinst' => $attrinst,
//                    'form' => &$form,
//                    'entity' => $entity,
//                );
                
                $values = $entity->$acode;
                if ( 1 != $attrinst->settings['input']['nov'] && !$input_method['isMultiValue'] ) {
                    natty_debug('Logic for multi-value attributes pending.');
                }
                else {
                    $widget = $callback_classname::$callback_method($attrinst, $form);
                    $widget['_default'] = $values;
                    $form->items['eav']['_data'][$acode] = $widget;
                }
                
            }
            
        }
        
    }
    
    public static function attachInstanceValues( AttrinstObject $attrinst, &$entity, array $record ) {
        
        $acode = $attrinst->acode;
        
        // Determine whether we have multiple storage columns
        $data_columns = $attrinst->settings['storage']['columns'];
        $value_is_array = ( 1 !== sizeof($data_columns) && 'value' !== $data_columns[0] );
        
        // Re-arrange values as array, if required
        if ( $value_is_array ):
            $record['value'] = array ();
            foreach ( $data_columns as $data_column ):
                $record['value'][$data_column] = $record[$data_column];
                unset ($record[$data_column]);
            endforeach;
        endif;
        
        // Single-value attribute?
        if ( 1 == $attrinst->settings['input']['nov'] ) {
            // Ignore all values except for the value at "0" index
            if ( isset ($entity->$acode) && !is_null($entity->$acode) )
                return;
            $entity->$acode = $record['value'];
        }
        // Multi-value attribute?
        else {
            if ( !isset ($entity->$acode) )
                $entity->$acode = array ();
            $values = $entity->$acode;
            $values[$record['vno']] = $record['value'];
            $entity->$acode = $values;
        }
    }
    
    /**
     * Saves values of an Attribute Instance for a given Entity
     * @param AttrinstObject $attrinst
     * @param \Natty\ORM\EntityObject $entity
     */
    public static function saveInstanceValues(AttrinstObject $attrinst, &$entity) {
        
        $tablename = $attrinst->settings['storage']['tablename'];
        $connection = \Natty::getDbo();
        
        $acode = $attrinst->acode;
        $eid = $entity->getId();
        $ail = 'UNDF';
        if ( $attrinst->isTranslatable && $lang_key = $entity->getHandler()->getKey('language') ):
            $ail = $entity->$lang_key;
        endif;
        
        // If the property is not loaded, do nothing!
        if ( !isset ($entity->$acode) )
            return;
        
        // See if the property has multiple values
        $values = ( 1 == $attrinst->settings['input']['nov'] )
                ? array ($entity->$acode) : $entity->$acode;
        
        // Re-index new values array
        $values = array_values($values);
        
        // @todo Remove only the values which were deleted?
        if ( !$entity->isNew ):
            $connection->delete($tablename, array (
                'key' => array (
                    'eid' => $eid,
                    'ail' => $ail,
                    'aiid' => $attrinst->aiid,
                )
            ));
        endif;
        
        // Determine whether we have multiple storage columns
        $data_columns = $attrinst->settings['storage']['columns'];
        $value_is_array = ( 1 !== sizeof($data_columns) && 'value' !== $data_columns[0] );
        
        // Upsert non-deleted values
        foreach ( $values as $vno => $value ):
            
            // Ignore value if empty
            if ( $value_is_array && 0 === sizeof($value) )
                continue;
            elseif ( 0 === strlen($value) )
                continue;
            
            $record = array (
                'egid' => $attrinst->egid,
                'aiid' => $attrinst->aiid,
                'eid' => $eid,
                'ail' => $ail,
                'vno' => $vno,
            );
            
            // Add values to the record
            if ( $value_is_array ) {
                foreach ( $data_columns as $data_column ):
                    if ( isset ($value[$data_column]) )
                        $record[$data_column] = $value[$data_column];
                endforeach;
            }
            else {
                $record['value'] = $value;
            }
            
            if ( $value_is_array )
                natty_debug($record, $attrinst->acode, $value_is_array);
            
            $connection->insert($tablename, $record);
            
        endforeach;
        
        // Attach the re-indexed values
        if ( $attrinst->settings['input']['nov'] != 1 )
            $entity->$acode = $values;
            
    }
    
    /**
     * Deletes values of an Attribute Instance for a given Entity
     */
    public static function deleteInstanceValues(AttrinstObject $attrinst, &$entity) {
        
        $tablename = $attrinst->settings['storage']['tablename'];
        
        \Natty::getDbo()->delete($tablename, array (
            'key' => array (
                'eid' => $entity->getId()
            ),
        ));
        
    }
    
    final public static function renderInstanceValues(AttrinstObject $attrinst, $entity, $view_mode = 'default') {
        
        // Attribute must be set
        $acode = $attrinst->acode;
        if ( !isset ($entity->$acode) )
            return;
        
        // Force values to be an array
        $values = $entity->$acode;
        if ( 1 == $attrinst->settings['input']['nov'] )
            $values = array ($values);
        
        // Must have output settings!
        if ( !isset ($attrinst->settings['output'][$view_mode]) ):
            trigger_error('Attribute output options not found for ' . $attrinst->acode . ', "' . $view_mode . '" view mode', E_USER_NOTICE);
            return;
        endif;
        $output_settings = $attrinst->settings['output'][$view_mode];
        $output_settings['entity'] = $entity;
        
        // Empty output method means attribute is hidden
        $omid = $output_settings['method'];
        if ( '' == $omid )
            return;
        
        // Call output method
        $omethod_helper = self::getOutputMethodHelper($omid);
        $callback_method = 'buildOutput';
        
        if ( !method_exists($omethod_helper, $callback_method) ) {
            $values_markup = 'Invalid output method.';
        }
        else {
            $values_markup = $omethod_helper::$callback_method($values, $output_settings);
        }
        
        // No value markup? Do not render anything.
        if ( !$values_markup )
            return;
        
        // Return a rarray
        $rarray = array (
            '_render' => 'element',
            '_element' => 'div',
            '_data' => array (
                'label' => '<div class="attr-label">' . $attrinst->name . ':</div> ',
                'values' => '<div class="attr-values">' . $values_markup . '</div>',
            ),
            'class' => array (
                'eav-attr',
                'attr-' . $attrinst->acode,
                'attr-type-' . $attrinst->dtid,
                'attr-label-' . $output_settings['label'],
            ),
        );
        
        return $rarray;
        
    }
    
    public static function getInputMethodHelper($imid) {
        $imid_parts = explode('--', $imid);
        return '\\Module\\' . ucfirst($imid_parts[0]) . '\\Classes\\Eav\\InputMethod_' . ucfirst($imid_parts[1]) . 'Helper';
    }
    
    public static function getOutputMethodHelper($omid) {
        $omid_parts = explode('--', $omid);
        return '\\Module\\' . ucfirst($omid_parts[0]) . '\\Classes\\Eav\\OutputMethod_' . ucfirst($omid_parts[1] . 'Helper');
    }
    
}