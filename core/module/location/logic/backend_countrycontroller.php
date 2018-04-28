<?php

namespace Module\Location\Logic;

class Backend_CountryController {
    
    public static function pageManage() {
        
        // Load dependencies
        $country_handler = \Natty::getHandler('location--country');
        
        // List head
        $list_head = array (
            array ('_data' => 'ISO3', 'width' => 80),
            array ('_data' => 'Name'),
            array ('_data' => 'Numeric Code', 'width' => 80),
            array ('_data' => 'ISO2', 'width' => 80),
            array ('_data' => 'Enabled?', 'width' => 80, '_column' => 'country.status', '_sortEnabled' => 1, '_sortMethod' => 'desc'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // Prepare list
        $query = $country_handler->getQuery();
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $paging_helper->setParameters($list_head);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
            'fetch' => array ('entity', 'location--country'),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $country ):
            
            $row = array ();
        
            $row[] = $country->cid;
            $row[] = '<div class="prop-title">' . $country->name . '</div>'
                    . '<div class="prop-description">Native name: ' . natty_vod($country->nativeName, '-') . '</div>';
            $row[] = $country->isoNumCode;
            $row[] = $country->iso2Code;
            $row[] = $country->status ? 'Yes' : '';
            $row['context-menu'] = $country->call('buildBackendLinks');
        
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/location/countries/create') . '" class="k-button">Create</a>',
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
    
    public static function pageForm($mode = NULL, $entity = NULL) {
        
        // Load dependencies
        $country_handler = \Natty::getHandler('location--country');
        $response = \Natty::getResponse();
        
        // Create mode
        if ( 'create' == $mode ) {
            $entity = $country_handler->create(array (
                'isNew' => 1,
                'ail' => \Natty::getInputLangId(),
            ));
            $response->attribute('title', 'Create country');
        }
        // Edit mode
        else {
            $response->attribute('title', 'Edit country');
        }
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'location-country-form',
            'i18n' => 1,
        ), array (
            'etid' => 'location--country',
            'entity' => &$entity,
        ));
        
        // Determine input language
        $input_lid = \Natty::readSetting('system--language');
        
        // Display form language
        $form->items['default']['_label'] = 'Basic Info';
        $form->items['default']['_data']['cid'] = array (
            '_label' => 'ISO3 Code',
            '_widget' => 'input',
            '_description' => '3 character ISO code for the country.',
            '_default' => $entity->cid,
            'class' => array ('widget-small'),
            'minlength' => 3,
            'maxlength' => 3,
            'required' => 1,
        );
        $form->items['default']['_data']['isoNumCode'] = array (
            '_label' => 'Numeric ISO Code',
            '_widget' => 'input',
            '_description' => 'ISO numeric code for the country.',
            '_default' => $entity->isoNumCode,
            'class' => array ('widget-small'),
            'maxlength' => 4,
            'required' => 1,
        );
        $form->items['default']['_data']['iso2Code'] = array (
            '_label' => 'ISO2 Code',
            '_widget' => 'input',
            '_description' => '2 character ISO code for the country.',
            '_default' => $entity->iso2Code,
            'class' => array ('widget-small'),
            'maxlength' => 2,
            'required' => 1,
        );
        $form->items['default']['_data']['nativeName'] = array (
            '_label' => 'Native name',
            '_widget' => 'input',
            '_description' => 'Name of the country as in it\'s native language.',
            '_default' => $entity->nativeName,
        );
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $entity->name,
            'required' => 1,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $entity->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_type' => 'submit',
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/location/countries'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted('save') ):
            
            $form_data = $form->getValues();
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $entity->setState($form_data);
            natty_debug($entity);
            $entity->save();
            
            \Natty\Console::success();
            
            $form->redirect = \Natty::url('backend/location/countries');
            $form->onProcess();
            
        endif;
        
        return $form->getRarray();
        
    }
    
}