<?php

namespace Module\Commerce\Logic;

class Backend_TaxGroupController {
    
    public static function pageManage() {
        
        // Load dependencies
        $taxgroup_handler = \Natty::getHandler('commerce--taxgroup');
        
        // Build query
        $query = $taxgroup_handler->getQuery()
                ->addSimpleCondition('status', -1, '!=');
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (),
            'fetch' => array ('entity', $taxgroup_handler->getEntityTypeId()),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $taxgroup ):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $taxgroup->name . '</div>';
            $row['context-menu'] = $taxgroup->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                'create' => '<a href="' . \Natty::url('backend/commerce/tax-groups/create') . '" class="k-button">Create</a>',
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
    
    public static function pageForm($mode, $taxgroup_id) {
        
        // Load dependencies
        $taxgroup_handler = \Natty::getHandler('commerce--taxgroup');
        
        // Create
        if ( 'create' === $mode ) {
            $taxgroup = $taxgroup_handler->create();
        }
        // Edit
        else {
            $taxgroup = $taxgroup_handler->readById($taxgroup_id);
        }
        
        // Bounce URL
        $bounce_url = \Natty::url('backend/commerce/tax-groups');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-taxgroup-form',
        ), array (
            'etid' => 'commerce--taxgroup',
            'entity' => &$taxgroup,
        ));
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_description' => 'Example: Luxury products or Consumer Electronics.',
            '_widget' => 'input',
            '_default' => $taxgroup->name,
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
            '_default' => $taxgroup->status,
            'class' => array ('options-inline'),
        );
        
        unset ($fs);
        
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
            
            $taxgroup->setState($form_data);
            $taxgroup->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output = array ();
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
}