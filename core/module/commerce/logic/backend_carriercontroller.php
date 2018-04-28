<?php

namespace Module\Commerce\Logic;

class Backend_CarrierController {
    
    public static function pageManage() {
        
        // Load dependencies
        $carrier_handler = \Natty::getHandler('commerce--carrier');
        
        // Build query
        $query = $carrier_handler->getQuery()
                ->addSimpleCondition('status', -1, '!=');
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $list_items = $carrier_handler->read(array (
            'conditions' => array (
                array ('AND', '1=1'),
            ),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_items as $carrier ):
            
            $row = array ();
            
            $col_title = '<div class="prop-title">' . $carrier->name . '</div>';
            if ( strlen($carrier->description) > 0 )
                $col_title .= '<div class="prop-description">' . $carrier->description . '</div>';
            $row[] = $col_title;
            
            $row['context-menu'] = $carrier->call('buildBackendLinks');
            
            $row = array (
                '_data' => $row,
                'class' => array (),
            );
            if (!$carrier->status)
                $row['class'][] = 'n-state-disabled';
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                'create' => '<a href="' . \Natty::url('backend/commerce/carriers/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $carrier_id) {
        
        // Load dependencies
        $carrier_handler = \Natty::getHandler('commerce--carrier');
        $ctype_handler = '\\Module\\Commerce\\Classes\\CarriertypeHandler';
        
        // Create
        if ( 'create' === $mode ) {
            $carrier = $carrier_handler->create();
        }
        // Edit
        else {
            $carrier = $carrier_handler->readById($carrier_id);
        }
        
        // Bounce URL
        $bounce_url = \Natty::url('backend/commerce/carriers');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-carrier-form',
            'i18n' => 1,
        ), array (
            'etid' => 'commerce--carrier',
            'entity' => &$carrier,
        ));
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_description' => 'Example: Free shipping, Home delivery, Fedex.',
            '_widget' => 'input',
            '_default' => $carrier->name,
            'maxlength' => 255,
            'required' => 1,
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_default' => $carrier->description,
        );
        
        // Carrier type
        $ctype_opts = $ctype_handler::readOptions();
        $form->items['default']['_data']['ctid'] = array (
            '_label' => 'Type',
            '_description' => 'This property cannot be changed once saved.',
            '_widget' => 'dropdown',
            '_options' => $ctype_opts,
            '_default' => $carrier->ctid,
            'placeholder' => ' ',
            'required' => 1,
        );
        
        $form->items['default']['_data']['isFree'] = array (
            '_label' => 'Is this free shipping?',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Yes',
                0 => 'No',
            ),
            '_default' => $carrier->isFree,
            'class' => array ('options-inline'),
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $carrier->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url,
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $carrier->setState($form_data);
            $carrier->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output = array ();
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
    public static function actionDelete($carrier) {
        
        $carrier->delete();
        
        \Natty\Console::success();
        
        \Natty::getResponse()->bounce();
        
    }
    
    public static function pageScope($carrier, $country = NULL, $state = NULL) {
        
        // Load dependencies
        $carrier_handler = \Natty::getHandler('commerce--carrier');
        $mod_location = \Natty::getPackage('module', 'location');
        $response = \Natty::getResponse();
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => 'Availability', 'class' => array ('size-medium')),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        $list_params = array (
            'cid' => $carrier->cid,
            'ail' => \Natty::getOutputLangId(),
        );
        
        // Decide level and build query
        if ($state) {
            $mode = 'region';
            $region_handler = \Natty::getHandler('location--region');
            $list_query = $region_handler->getQuery()
                    ->addColumn('status carrierStatus', 'scope')
                    ->addJoin('left', '%__commerce_carrier_scope scope', array (
                        array ('AND', '{scope}.{cid} = :cid'),
                        array ('AND', '{scope}.{idState} = :idState'),
                        array ('AND', '{scope}.{idRegion} = {region}.{rid}'),
                    ))
                    ->addSimpleCondition('region.sid', ':idState')
                    ->orderBy('ISNULL(scope.status)')
                    ->orderBy('scope.status', 'DESC')
                    ->orderBy('region_i18n.name');
            $list_params['idState'] = $state->sid;
            $response->attribute('title', $carrier->name . ' in ' . $state->name);
        }
        elseif ($country) {
            $mode = 'state';
            $state_handler = \Natty::getHandler('location--state');
            $list_query = $state_handler->getQuery()
                    ->addColumn('status carrierStatus', 'scope')
                    ->addJoin('left', '%__commerce_carrier_scope scope', array (
                        array ('AND', '{scope}.{cid} = :cid'),
                        array ('AND', '{scope}.{idCountry} = :idCountry'),
                        array ('AND', '{scope}.{idState} = {state}.{sid}'),
                        array ('AND', '{scope}.{idRegion} = 0'),
                    ))
                    ->addSimpleCondition('state.cid', ':idCountry')
                    ->orderBy('ISNULL(scope.status)')
                    ->orderBy('scope.status', 'DESC')
                    ->orderBy('state_i18n.name');
            $list_params['idCountry'] = $country->cid;
            $response->attribute('title', $carrier->name . ' in ' . $country->name);
        }
        else {
            $mode = 'country';
            $country_handler = \Natty::getHandler('location--country');
            $list_query = $country_handler->getQuery()
                    ->addColumn('status carrierStatus', 'scope')
                    ->addJoin('left', '%__commerce_carrier_scope scope', array (
                        array ('AND', '{scope}.{cid} = :cid'),
                        array ('AND', '{scope}.{idCountry} = {country}.{cid}'),
                        array ('AND', '{scope}.{idState} = 0'),
                        array ('AND', '{scope}.{idRegion} = 0'),
                    ))
                    ->orderBy('ISNULL(scope.status)')
                    ->orderBy('scope.status', 'DESC')
                    ->orderBy('country_i18n.name');
            $response->attribute('title', $carrier->name . ': Countries');
        }
        
        // List body
        $paging_helper = new \Natty\Helper\PagingHelper($list_query);
        $list_data = $paging_helper->execute(array (
            'parameters' => $list_params,
            'fetch' => array ('entity', 'location--' . $mode),
        ));
        
        $list_body = array ();
        foreach ($list_data['items'] as $item):
            
            $row = array ();
        
            $row[] = '<div class="prop-title">' . $item->name . '</div>';
            
            // Availability options
            $col_avail = array (
                '_render' => 'form_item',
                '_widget' => 'dropdown',
                '_options' => array (
                    0 => '',
                    1 => 'In selected areas',
                    2 => 'Everywhere',
                ),
                '_default' => $item->carrierStatus,
                'name' => 'items.' . $item->getId(),
                'class' => array ('prop-status'),
                'data-type' => $mode,
                'data-id' => $item->getId(),
            );
            if ( 'region' === $mode ):
                unset ($col_avail['_options'][1]);
            endif;
            $row[] = $col_avail;
            
            // Link to configure sub-parts
            switch ($mode):
                case 'country':
                    $row[] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $carrier->cid . '/scope/' . $item->cid) . '" class="k-button">Configure</a>';
                    break;
                case 'state':
                    $row[] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $carrier->cid . '/scope/' . $item->cid . '/' . $item->sid) . '" class="k-button">Configure</a>';
                    break;
                case 'region':
                    $row[] = '';
            endswitch;
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $mod_commerce = \Natty::getPackage('module', 'commerce');
        $response->addScript(array (
            'src' => NATTY_BASE . $mod_commerce->path . '/reso/backend.js',
        ));
        
        $output = array ();
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            'id' => 'commerce-carrier-scope-list',
            'data-cid' => $carrier->cid,
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        return $output;
        
    }
    
    public static function pageConfigure($carrier) {
        
        // Load dependencies
        $response = \Natty::getResponse();
        
        // Call the carrier-type helper
        $ctype = \Module\Commerce\Classes\CarriertypeHandler::readById($carrier->ctid);
        if ( !$ctype )
            \Natty::error(500);
        
        // Prepare response
        $response->attribute('title', $carrier->name . ': Configure');
        $output = call_user_func($ctype->configCallback, $carrier);
        return $output;
        
    }
    
}