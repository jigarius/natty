<?php

namespace Module\System\Logic;

class Backend_CurrencyController {
    
    public static function pageManage() {
        
        // Load dependencies
        $currency_handler = \Natty::getHandler('system--currency');
        
        // List head
        $list_head = array (
            array ('_data' => 'Code', 'class' => array ('size-small')),
            array ('_data' => 'Native name'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // Prepare list
        $query = $currency_handler->getQuery();
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute();
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $record ):
            
            $record = $currency_handler->prepareForUsage($record);
            $currency = $currency_handler->create($record);
            
            $row = array ();
        
            $row[] = $currency->cid;
            $row[] = '<div class="prop-title">' . $currency->nativeName . '</div>'
                    . '<div class="prop-description">Preview: ' . natty_format_money(9999.999, array (
                        'currency' => $currency->cid,
                    )) . '</div>';
            $row['context-menu'] = $currency->call('buildBackendLinks');
        
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/system/currencies/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode = NULL, $entity = NULL) {
        
        // Load dependencies
        $currency_handler = \Natty::getHandler('system--currency');
        $response = \Natty::getResponse();
        
        // Create mode
        if ( 'create' == $mode ) {
            $entity = $currency_handler->create();
            $entity->isNew = 1;
            $response->attribute('title', 'Create currency');
        }
        // Edit mode
        else {
            $response->attribute('title', 'Edit currency');
        }
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-currency-form',
        ), array (
            'entity' => &$entity,
        ));
        
        $form->items['default']['_label'] = 'Basic Info';
        $form->items['default']['_data']['cid'] = array (
            '_label' => 'Currency Code',
            '_widget' => 'input',
            '_description' => 'A unique 3 character currency code.',
            '_default' => $entity->cid,
            'class' => array ('widget-small'),
            'maxlength' => 3,
        );
        $form->items['default']['_data']['nativeName'] = array (
            '_label' => 'Native name',
            '_widget' => 'input',
            '_description' => 'Name of the currency as in the native language.',
            '_default' => $entity->nativeName,
        );
        
        // Exchange rate
        $form->items['default']['_data']['xRate'] = array (
            '_label' => 'Exchange rate',
            '_widget' => 'input',
            '_suffix' => ' = 1 ' . \Natty::readSetting('system--currency'),
            '_default' => $entity->xRate,
            'step' => .001,
            'type' => 'number',
            'class' => array ('widget-small'),
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
        
        $form->items['advanced'] = array (
            '_widget' => 'container',
            '_label' => 'Advanced Info',
        );
        $form->items['advanced']['_data']['unitSymbol'] = array (
            '_label' => 'Unit symbol',
            '_widget' => 'input',
            '_description' => 'Example: $ or &pound;',
            '_default' => $entity->unitSymbol,
            'class' => array ('widget-small'),
        );
        $form->items['advanced']['_data']['unitFirst'] = array (
            '_label' => 'Unit position',
            '_widget' => 'options',
            '_options' => array (
                1 => '$999',
                0 => '999$',
            ),
            '_default' => $entity->unitPosition,
            'class' => array ('options-inline'),
        );
        $form->items['advanced']['_data']['unitSpacing'] = array (
            '_label' => 'Unit spacing',
            '_widget' => 'options',
            '_options' => array (
                1 => '$ 999',
                0 => '$999',
            ),
            '_default' => $entity->unitSpacing,
            'class' => array ('options-inline'),
        );
        $form->items['advanced']['_data']['decimalSymbol'] = array (
            '_label' => 'Decimal symbol',
            '_widget' => 'input',
            '_description' => 'A symbol which represents the decimal (dot) as in $999.99',
            '_default' => $entity->decimalSymbol,
            'class' => array ('widget-small'),
        );
        $form->items['advanced']['_data']['decimalPlaces'] = array (
            '_label' => 'Decimal places',
            '_widget' => 'input',
            '_description' => 'The number of places after decimal.',
            '_default' => $entity->decimalPlaces,
            'class' => array ('widget-small'),
        );
        $form->items['advanced']['_data']['thouSeparator'] = array (
            '_label' => 'Thousand separator',
            '_widget' => 'input',
            '_description' => 'The symbol which separates thousands as in $1,000,000',
            '_default' => $entity->thouSeparator,
            'class' => array ('widget-small'),
        );
        
        $form->actions['save'] = array (
            '_type' => 'submit',
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_type' => 'anchor',
            '_label' => 'Back',
            'href' => \Natty::url('backend/system/currencies'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_values = $form->getValues();
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $entity->setState($form_values);
            $entity->save();
            
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            
            $form->redirect = \Natty::url('backend/system/currencies');
            $form->onProcess();
            
        endif;
        
        return $form->getRarray();
        
    }
    
}