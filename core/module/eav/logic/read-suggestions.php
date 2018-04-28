<?php

defined('NATTY') or die;

list ($attrinst) = $arguments;

$input_settings = $attrinst->settings['input'];

// Load libraries
$etype_handler = \Natty::getHandler($input_settings['etid']);
$output = array ();

// Read involved properties
$label_key = $etype_handler->getKey('label');
$label_definition = $etype_handler->getPropertyDefinition($label_key);
$id_key = $etype_handler->getKey('id');
$id_definition = $etype_handler->getPropertyDefinition($label_key);

// The properties which can be filtered
$filterable_properties = array ($id_key, $label_key);

// Build a read query
$query = \Natty::getDbo()
        ->getQuery('select', $etype_handler->tableName . ' ' . $etype_handler->modelCode)
        ->orderBy($label_key, 'asc')
        ->limit(10);
$read_options = array (
    'conditions' => array (),
    'parameters' => array (),
);

// Add entity-group restriction
if ( $input_settings['egid'] ):
    
    $group_key = $etype_handler->getKey('group');
    $query->addComplexCondition(array ($etype_handler->modelCode . '.' . $group_key, 'IN', $input_settings['egid']));
    
endif;

// Read the id column
$query->addColumn($id_key, $etype_handler->modelCode);

// Read the label column
if ( isset ($label_definition['isTranslatable']) ) {
    $query->addColumn($label_key, $etype_handler->modelCode . '_i18n');
    $query->addJoin('INNER', $etype_handler->i18nTableName . ' ' . $etype_handler->modelCode . '_i18n', array ($etype_handler->modelCode . '_i18n.' . $id_key, '=', $etype_handler->modelCode . '.' . $id_key));
    $query->addComplexCondition(array ('ail', '=', ':ail'));
    $read_options['parameters']['ail'] = \Natty::getOutputLangId();
}
else {
    $query->addColumn($label_key, $etype_handler->tableName);
}

// Determine filters
if ( isset ($_REQUEST['filter']) && isset ($_REQUEST['filter']['filters']) ):
    
    $list_filters = $_REQUEST['filter']['filters'];
    
    foreach ( $list_filters as $filter_key => $filter_data ):
        
        // Fieldname must be filterable
        if ( !in_array($filter_data['field'], $filterable_properties) )
            continue;
        
        // Determine tablename for the property
        $field_definition = $etype_handler->getPropertyDefinition($filter_data['field']);
        if ( !$field_definition )
            continue;
        $filter_data['column'] = $etype_handler->tableName;
        if ( isset ($field_definition['isTranslatable']) )
            $filter_data['column'] .= '_i18n';
        $filter_data['column'] .= '.' . $filter_data['field'];
        
        // Add conditions
        switch ( $filter_data['operator'] ):
            case 'startswith':
                $this_condition = array ('AND', array ($filter_data['column'], 'LIKE', ':f' . $filter_key));
                $read_options['conditions'][] = $this_condition;
                $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                break;
            case 'equals':
                if ( is_array($filter_data['value']) ) {
                    $this_condition = array ('AND', array ($filter_data['column'], 'IN', $filter_data['value']));
                    $read_options['conditions'][] = $this_condition;
                }
                else {
                    $this_condition = array ('AND', array ($filter_data['column'], '=', ':f' . $filter_key));
                    $read_options['conditions'][] = $this_condition;
                    $read_options['parameters']['f' . $filter_key] = $filter_data['value'];
                }
                break;
        endswitch;
        
    endforeach;
    
endif;

// Read data
//$query->preview();
$options_coll = $query->addComplexCondition($read_options['conditions'])
//        ->preview()
        ->execute($read_options['parameters'])
        ->fetchAll();

echo json_encode($options_coll);
exit;