<?php

namespace Module\Commerce\Logic;

class Backend_TaxController {
    
    public static function pageManage() {
        
        // Load dependencies
        $tax_handler = \Natty::getHandler('commerce--tax');
        
        // Build query
        $query = $tax_handler->getQuery();
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => 'Rate', 'class' => array ('size-small', 'n-ta-ri')),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
            'fetch' => array ('entity', $tax_handler->getEntityTypeId()),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $tax ):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $tax->name . '</div>';
            $row[] = array (
                'class' => array ('n-ta-ri'),
                '_data' => $tax->rate,
            );
            $row['context-menu'] = $tax->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                'create' => '<a href="' . \Natty::url('backend/commerce/taxes/create') . '" class="k-button">Create</a>',
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
    
    public static function pageForm($mode, $tax_id) {
        
        // Load dependencies
        $tax_handler = \Natty::getHandler('commerce--tax');
        $ilid = \Natty::getInputLangId();
        
        // Create
        if ( 'create' === $mode ) {
            
            $tax = $tax_handler->create(array (
                'ail' => $ilid,
            ));
            
        }
        // Edit
        else {
            
            $tax = $tax_handler->readById($tax_id, array (
                'language' => $ilid,
            ));
            
        }
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-tax-form',
        ), array (
            'etid' => 'commerce--tax',
            'entity' => &$tax,
        ));
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_description' => 'Example: India VAT 12% or Malaysia GST 4%.',
            '_widget' => 'input',
            '_default' => $tax->name,
            'maxlength' => 255,
            'required' => 1,
        );
        $form->items['default']['_data']['rate'] = array (
            '_label' => 'Rate',
            '_description' => 'Enter tax rate in percentage. Example: 12.5 or 8.255.',
            '_widget' => 'input',
            '_suffix' => '%',
            '_default' => $tax->rate,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
            'maxlength' => 255,
            'required' => 1,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $tax->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $tax->setState($form_data);
            $tax->save();
            
            \Natty\Console::success();
            $form->redirect = \Natty::url('backend/commerce/taxes');
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output = array ();
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
}