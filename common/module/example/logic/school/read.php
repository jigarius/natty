<?php

defined('NATTY') or die;

$read_options = array (
    'conditions' => array (
        array ('AND', '1=1')
    ),
    'parameters' => array (),
    'limit' => 10,
    'ordering' => array ('name' => 'asc')
);

if ( isset ($_REQUEST['filter']) && isset ($_REQUEST['filter']['filters']) ):
    
    $list_filters = $_REQUEST['filter']['filters'];
    
    foreach ( $list_filters as $filter_key => $filter_data ):
        
        // Fieldname must be filterable
        if ( !in_array($filter_data['field'], array ('sid', 'name')) )
            continue;
        
        switch ( $filter_data['operator'] ):
            case 'startswith':
                $this_condition = array ('AND', array ($filter_data['field'], 'LIKE', ':f' . $filter_key));
                $read_options['conditions'][] = $this_condition;
                $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                break;
            case 'equals':
                if ( is_array($filter_data['value']) ) {
                    $this_condition = array ('AND', array ($filter_data['field'], 'IN', $filter_data['value']));
                    $read_options['conditions'][] = $this_condition;
                }
                else {
                    $this_condition = array ('AND', array ($filter_data['field'], 'LIKE', ':f' . $filter_key));
                    $read_options['conditions'][] = $this_condition;
                    $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                }
                break;
        endswitch;
        
    endforeach;
    
endif;

// Read data
$options_coll = \Natty::getDbo()->read('%__example_school', $read_options);

echo json_encode($options_coll);
exit;