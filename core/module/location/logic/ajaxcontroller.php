<?php

namespace Module\Location\Logic;

class AjaxController {
    
    public static function actionServiceState(&$output) {
        
        $request = \Natty::getRequest();
        
        switch ( $action = $request->getString('do') ):
            case 'read':
                
                $read_options = array (
                    'properties' => array ('sid', 'name'),
                    'conditions' => array (
                        array ('AND', '1=1'),
                    ),
                    'parameters' => array (),
                    'limit' => 10,
                    'ordering' => array ('name' => 'asc'),
                    'format' => $request->getString('format', 'array'),
                );
                
                // Specify country id
                if ( isset ($_REQUEST['cid']) ):
                    
                    $filter_cid = $_REQUEST['cid'];
                    if ( !is_array($filter_cid) )
                        $filter_cid = array ($filter_cid);
                    $read_options['conditions'][] = array ('AND', array ('state.cid', 'IN', $filter_cid));
                    
                endif;

                if ( isset ($_REQUEST['filter']) && isset ($_REQUEST['filter']['filters']) ):

                    $list_filters = $_REQUEST['filter']['filters'];
                    foreach ( $list_filters as $filter_key => $filter_data ):

                        // Fieldname must be filterable
                        if ( !in_array($filter_data['field'], array ('cid', 'sid', 'name')) )
                            continue;

                        $filter_data['tableName'] = 'state';
                        if (in_array($filter_data['field'], array ('name')))
                            $filter_data['tableName'] = 'state_i18n';
                        
                        switch ( $filter_data['operator'] ):
                            case 'startswith':
                                $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'LIKE', ':f' . $filter_key));
                                $read_options['conditions'][] = $this_condition;
                                $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                                break;
                            case 'equals':
                                if ( is_array($filter_data['value']) ) {
                                    $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'IN', $filter_data['value']));
                                    $read_options['conditions'][] = $this_condition;
                                }
                                else {
                                    $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'LIKE', ':f' . $filter_key));
                                    $read_options['conditions'][] = $this_condition;
                                    $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                                }
                                break;
                        endswitch;

                    endforeach;

                endif;

                // Read data
                $options_coll = \Natty::getHandler('location--state')
                        ->readOptions($read_options);
                $output['data'] = $options_coll;
                
                break;
                
        endswitch;
        
    }
    
    public static function actionServiceRegion(&$output) {
        
        $request = \Natty::getRequest();
        
        switch ( $action = $request->getString('do') ):
            case 'read':
                
                $read_options = array (
                    'properties' => array ('rid', 'name'),
                    'conditions' => array (
                        array ('AND', '1=1'),
                    ),
                    'parameters' => array (),
                    'limit' => 10,
                    'ordering' => array ('name' => 'asc'),
                    'format' => $request->getString('format', 'array'),
                );
                
                // Specify state id
                if ( isset ($_REQUEST['sid']) ):
                    
                    $filter_sid = $_REQUEST['sid'];
                    $filter_sid = explode(',', $filter_sid);
                    $read_options['conditions'][] = array ('AND', array ('region.sid', 'IN', $filter_sid));
                    
                endif;

                if ( isset ($_REQUEST['filter']) && isset ($_REQUEST['filter']['filters']) ):

                    $list_filters = $_REQUEST['filter']['filters'];
                    foreach ( $list_filters as $filter_key => $filter_data ):

                        // Fieldname must be filterable
                        if ( !in_array($filter_data['field'], array ('sid', 'rid', 'name')) )
                            continue;

                        $filter_data['tableName'] = 'region';
                        if (in_array($filter_data['field'], array ('name')))
                            $filter_data['tableName'] = 'region_i18n';
                        
                        switch ( $filter_data['operator'] ):
                            case 'startswith':
                                $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'LIKE', ':f' . $filter_key));
                                $read_options['conditions'][] = $this_condition;
                                $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                                break;
                            case 'equals':
                                if ( is_array($filter_data['value']) ) {
                                    $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'IN', $filter_data['value']));
                                    $read_options['conditions'][] = $this_condition;
                                }
                                else {
                                    $this_condition = array ('AND', array ($filter_data['tableName'] . '.' . $filter_data['field'], 'LIKE', ':f' . $filter_key));
                                    $read_options['conditions'][] = $this_condition;
                                    $read_options['parameters']['f' . $filter_key] = $filter_data['value'] . '%';
                                }
                                break;
                        endswitch;

                    endforeach;

                endif;

                // Read data
                $options_coll = \Natty::getHandler('location--region')
                        ->readOptions($read_options);
                
                $output['data'] = $options_coll;
                
                break;
                
        endswitch;
        
    }
    
}