<?php

namespace Module\Cms\Logic;

class Backend_ContentTypeController {
    
    public static function pageManage() {
        
        // Read existing content-types
        $ctype_handler = \Natty::getHandler('cms--contenttype');
        $ctypes = $ctype_handler->read(array (
            'ordering' => array ('name' => 'asc')
        ));

        // Build a list
        $list_head = array (
            array ('_data' => 'Content Type'),
            array ('_data' => '', 'class' => array ('context-menu'))
        );

        $list_body = array ();
        foreach ( $ctypes as $ctype ):

            $row = array ();
            $row[] = '<span class="prop-title">' . $ctype->name . '</span>'
                . ($ctype->description ? '<div class="prop-description">' . $ctype->description . '</div>' : '');

            $row['context-menu'] = $ctype->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/cms/content-type/create') . '" class="k-button">Create</a>'
            )
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $ctype) {
        
        // Load libraries
        $ctype_handler = \Natty::getHandler('cms--contenttype');

        // Creation?
        if ( 'create' == $mode ) {
            $ctype = $ctype_handler->create(array (
                'module' => 'cms',
                'isNew' => 1,
                'isCustom' => 1,
            ));
        }
        // Modification?
        else {

        }

        $bounce_url = \Natty::url('backend/cms/content-types');

        // Build form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'cms-contenttype-form'
        ));
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info'
        );
        $form->items['basic']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            '_description' => 'A name for the content type. Example: <strong>Page</strong> or <strong>Article</strong>.',
            'required' => TRUE,
            '_default' => $ctype->name,
        );
        $form->items['basic']['_data']['ctid'] = array (
            '_widget' => 'iname',
            '_default' => $ctype->ctid,
            '_iname' => array (
                'base' => 'name',
                'conflict' => function($ctid) {
                    return \Natty::getEntity('cms--contenttype', $ctid);
                }
            ),
        );
        if ( 'edit' == $mode ):
            $form->items['basic']['_data']['ctid']['readonly'] = 'readonly';
        endif;
        $form->items['basic']['_data']['description'] = array (
            '_widget' => 'textarea',
            '_label' => 'Description',
            '_default' => $ctype->description,
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
            'type' => 'submit',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url,
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form_values = $form->getValues();

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_data = $form->getValues();
            $ctype->setState($form_data);
            $ctype->save();

            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();

        endif;

        // Prepare response
        $output = $form->getRarray();
        return $output;
        
    }
    
}