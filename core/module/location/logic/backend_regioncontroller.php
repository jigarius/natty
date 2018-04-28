<?php

namespace Module\Location\Logic;

class Backend_RegionController {
    
    public static function pageManage($state, $country) {
        
        // Load dependencies
        $region_handler = \Natty::getHandler('location--region');
        
        // List head
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => 'Enabled?', 'width' => 80, '_column' => 'region.status', '_sortEnabled' => 1, '_sortMethod' => 'desc'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // Prepare list
        $query = $region_handler->getQuery()
                ->addSimpleCondition('region.sid', ':sid');
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $paging_helper->setParameters($list_head);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
                'sid' => $state->sid,
            ),
            'fetch' => array ('entity', 'location--region'),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $region ):
            
            $row = array ();
        
            $row[] = '<div class="prop-title">' . $region->name . '</div>';
            $row[] = $region->status ? 'Yes' : '';
            $row['context-menu'] = $region->call('buildBackendLinks');
        
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/location/countries/' . $country->cid . '/states/' . $state->sid . '/regions/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $region_id = NULL, $state = NULL) {
        
        // Load dependencies
        $region_handler = \Natty::getHandler('location--region');
        $response = \Natty::getResponse();
        
        // Create mode
        if ( 'create' == $mode ) {
            $region = $region_handler->create(array (
                'sid' => $state->sid,
            ));
            $region->isNew = 1;
            $response->attribute('title', 'Create region');
        }
        // Edit mode
        else {
            $region = \Natty::getEntity('location--region', $region_id, array (
                'language' => \Natty::getInputLangId(),
            ));
            if ( !$region )
                \Natty::error(400);
            $response->attribute('title', 'Edit region');
        }
        
        // Bounce
        $bounce_url = \Natty::url('backend/location/countries/' . $state->cid . '/states/' . $state->sid . '/regions');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'location-region-form',
            'i18n' => TRUE,
        ), array (
            'etid' => 'location--region',
            'entity' => &$region,
        ));
        
        // Display form language
        $form->items['default']['_label'] = 'Basic Info';
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $region->name,
            'required' => 1,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $region->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_type' => 'submit',
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url,
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted('save') ):
            
            $form_data = $form->getValues();
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $region->setState($form_data);
            $region->save();
            
            \Natty\Console::success();
            
            $form->redirect = $bounce_url;
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function actionDelete($region) {
        
        $region->delete();
        
        \Natty::getResponse()->bounce();
        
    }
    
}