<?php

namespace Module\Listing\Logic;

class Backend_ListController {
    
    public static function pageManage() {
        
        // Load dependencies
        $list_handler = \Natty::getHandler('listing--list');
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $list_data = $list_handler->read(array (
            'ordering' => array (
                'name' => 'asc',
            ),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data as $list ):
            
            $row = array ();
            $row[]= '<div class="prop-title">' . $list->name . '</div>'
                . '<div class="prop-description">' . $list->description . '</div>';
            $row['context-menu'] = $list->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/listing/import') . '" class="k-button">Import</a>',
                '<a href="' . \Natty::url('backend/listing/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $lid) {
        
        // Load dependencies
        $list_handler = \Natty::getHandler('listing--list');
        $etype_handler = \Natty::getHandler('system--entitytype');
        $response = \Natty::getResponse();
        $mod_listing = \Natty::getPackage('module', 'listing');
        $output = array ();
        
        // Creation
        if ( 'create' == $mode ) {
            $list = $list_handler->create();
            $response->attribute('title', 'Create list');
        }
        // Modify
        else {
            $list = $list_handler->readById($lid, array (
                'language' => \Natty::getInputLangId(),
            ));
            $response->attribute('title', 'Edit list');
        }
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'listing-list-create-form',
        ), array (
            'etid' => 'listing--list',
            'egid' => NULL,
            'entity' => &$list,
        ));
        
        $form->items['default']['_label'] = 'Basic info';
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_description' => 'This would be displayed on administrative pages.',
            '_widget' => 'input',
            '_default' => $list->name,
            'maxlength' => 255,
            'required' => 1,
        );
        $form->items['default']['_data']['lcode'] = array (
            '_widget' => 'iname',
            '_iname' => array (
                'base' => 'name',
                'conflictCallback' => array ($list_handler, 'readById'),
            ),
            '_default' => $list->lcode,
            'readonly' => (bool) $list->lid,
            'maxlength' => 64,
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'input',
            '_default' => $list->description,
            'maxlength' => 255,
            'required' => 1,
        );
        
        // Entity type
        $etype_opts = $etype_handler->readOptions(array ());
        $form->items['default']['_data']['settings.etid'] = array (
            '_label' => 'Entity type',
            '_description' => 'Choose the type of entities you wish to list.',
            '_widget' => 'dropdown',
            '_options' => $etype_opts,
            '_default' => $list->settings['etid'],
            'placeholder' => '',
            'required' => 1,
        );
        
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $list->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
            '_label' => $list->lid ? 'Save' : 'Save & continue',
            'type' => 'submit',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/listing'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $list->setState($form_data);
            $list->save();
            
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            $form->redirect = \Natty::url('backend/listing/' . $list->lid);
            
            $form->onProcess();
            
        endif;

        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function pageImport() {
        
        // Load dependencies
        $list_handler = \Natty::getHandler('listing--list');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'listing-list-import-form',
        ));
        $form->items['default']['_data']['code'] = array (
            '_label' => 'Code',
            '_description' => 'Type or paste the import code here.',
            '_widget' => 'textarea',
            'rows' => 20,
            'required' => 1,
        );
        $form->items['default']['_data']['overwrite'] = array (
            '_label' => 'Overwrite',
            '_description' => 'Overwrite conflicting list definition.',
            '_widget' => 'input',
            'type' => 'checkbox',
            '_default' => 0,
        );
        
        $form->actions['import'] = array (
            '_label' => 'Import',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/listing'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
        
            $code = explode("\r\n", $form_data['code']);
            foreach ( $code as $lno => $line ):
                
                // Ignore comments
                if ( 0 === strpos($line, '//') )
                    unset ($code[$lno]);
                
                // Ignore empty lines
                if ( 0 === strlen($line) )
                    unset ($code[$lno]);
                
            endforeach;
            $code = implode("\r\n", $code);
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $state = NULL;
            eval($code);
            $list = $list_handler->create($state);
            
            // Delete conflicting list
            if ( $form_data['overwrite'] ):
                $conflict = $list_handler->readById($list->lcode);
                if ( $conflict )
                    $conflict->delete();
            endif;
            
            $list->save();
            
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            
            $form->redirect = \Natty::url('backend/listing');
            $form->onProcess();
            
        endif;
        
        // Prepare output
        return $form->getRarray();
        
    }
    
}