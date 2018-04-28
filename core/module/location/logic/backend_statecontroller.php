<?php

namespace Module\Location\Logic;

class Backend_StateController {
    
    public static function pageManage($country) {
        
        // Load dependencies
        $state_handler = \Natty::getHandler('location--state');
        $response = \Natty::getResponse();
        
        // List head
        $list_head = array (
            array ('_data' => 'Code', 'width' => 80),
            array ('_data' => 'Name'),
            array ('_data' => 'Enabled?', 'width' => 80, '_column' => 'state.status'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // Prepare query
        $query = $state_handler->getQuery();
        $query->addSimpleCondition('cid', ':cid');
        
        // Prepare list
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $paging_helper->setParameters($list_head);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
                'cid' => $country->cid,
            ),
            'fetch' => array ('entity', 'location--state'),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $state ):
            
            $row = array ();
        
            $row[] = $state->scode;
            $row[] = '<div class="prop-title">' . $state->name . '</div>';
            $row[] = $state->status ? 'Yes' : '';
            $row['context-menu'] = $state->call('buildBackendLinks');
        
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $response->attribute('title', $country->name . ': States');
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/location/countries/' . $country->cid . '/states/create') . '" class="k-button">Create</a>',
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
    
    public static function pageForm($mode = NULL, $state_id, $country = NULL) {
        
        // Load dependencies
        $country_handler = \Natty::getHandler('location--country');
        $state_handler = \Natty::getHandler('location--state');
        $response = \Natty::getResponse();
        
        // Create mode
        if ( 'create' == $mode ) {
            $state = $state_handler->create(array (
                'cid' => $country->cid,
                'ail' => \Natty::getInputLangId(),
            ));
            $state->isNew = 1;
            $response->attribute('title', 'Create state');
        }
        // Edit mode
        else {
            $state = $state_handler->readById($state_id, array (
                'language' => \Natty::getInputLangId(),
            ));
            $country = $country_handler->readById($state->cid, array (
                'language' => \Natty::getInputLangId(),
            ));
            $response->attribute('title', 'Edit state');
        }
        
        // Bounce url
        $bounce_url = \Natty::url('backend/location/countries/' . $country->cid . '/states');
        if ( isset ($_REQUEST['bounce']) )
            $bounce_url = $_REQUEST['bounce'];
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-state-form',
            'i18n' => TRUE,
        ), array (
            'etid' => 'location--state',
            'entity' => &$state,
        ));
        
        $form->items['default']['_label'] = 'Basic Info';
        $form->items['default']['_data']['scode'] = array (
            '_label' => 'State code',
            '_widget' => 'input',
            '_description' => 'A unique code for the state. This is usually prefixed with the country code.',
            '_default' => $state->scode,
            'class' => array ('widget-small'),
            'maxlength' => 8,
        );
        $form->items['default']['_data']['cid'] = array (
            '_label' => 'Country',
            '_description' => 'The country where this state / territory is located.',
            '_widget' => 'dropdown',
            '_options' => $country_handler->readOptions(),
            '_default' => $state->cid,
            'placeholder' => '',
        );
        $form->items['default']['_data']['nativeName'] = array (
            '_label' => 'Native name',
            '_widget' => 'input',
            '_description' => 'Name of the state as in the native language.',
            '_default' => $state->nativeName,
        );
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $state->name,
            'required' => 1,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $state->status,
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
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $state->setState($form_data);
            $state->save();
            
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            
            $form->redirect = $bounce_url;
            $form->onProcess();
            
        endif;
        
        return $form->getRarray();
        
    }
    
    public static function pageAction() {
        
        $action = \Natty::getRequest()->getString('do');
        $method = 'action' . natty_strtocamel($action, TRUE);
        
        if ( !method_exists(__CLASS__, $method) )
            \Natty::error(500);
        
        return self::$method();
        
    }
    
}