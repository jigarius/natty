<?php

namespace Module\Commerce\Logic;

class AjaxController {
    
    public static function actionCarrierScope(&$output) {
        
        $dbo = \Natty::getDbo();
        $tablename = '%__commerce_carrier_scope';
        
        // Prepare data
        $data = $_POST;
        $data['status'] = (int) $data['status'];
        
        switch ($data['type']):
            case 'country':
                
                $country = \Natty::getEntity('location--country', $data['id']);
                if ( !$country )
                    \Natty::error(400);
                
                $dbo->upsert($tablename, array (
                    'cid' => $data['cid'],
                    'idCountry' => $country->cid,
                    'idState' => 0,
                    'idRegion' => 0,
                    'status' => $data['status'],
                ), array (
                    'keys' => array ('cid', 'idCountry', 'idState', 'idRegion')
                ));
                
                $output['_message'] = NATTY_ACTION_SUCCEEDED;
                
                break;
            case 'state':
                
                $state = \Natty::getEntity('location--state', $data['id']);
                if ( !$state )
                    \Natty::error(400);
                
                $dbo->upsert($tablename, array (
                    'cid' => $data['cid'],
                    'idCountry' => $state->cid,
                    'idState' => $state->sid,
                    'idRegion' => 0,
                    'status' => $data['status'],
                ), array (
                    'keys' => array ('cid', 'idCountry', 'idState', 'idRegion')
                ));
                
                $output['_message'] = NATTY_ACTION_SUCCEEDED;
                
                break;
            case 'region':
                
                $region = \Natty::getEntity('location--region', $data['id']);
                if ( !$region )
                    \Natty::error(400);
                
                $dbo->upsert($tablename, array (
                    'cid' => $data['cid'],
                    'idCountry' => $region->cid,
                    'idState' => $region->sid,
                    'idRegion' => $region->rid,
                    'status' => $data['status'],
                ), array (
                    'keys' => array ('cid', 'idCountry', 'idState', 'idRegion')
                ));
                
                $output['_message'] = NATTY_ACTION_SUCCEEDED;
                
                break;
        endswitch;
        
    }
    
}