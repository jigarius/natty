<?php

namespace Module\Contact\Logic;

class Backend_CategoryController {
    
    public static function pageManage() {
        
        // Load dependencies
        $category_handler = \Natty::getHandler('contact--category');
        
        $category_coll = $category_handler->read(array (
            'key' => array ('status' => 1),
            'ordering' => array ('name' => 'asc'),
        ));

        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => '&nbsp;', 'class' => array ('context-menu')),
        );
        $list_body = array ();

        foreach ( $category_coll as $category ):

            $row = array ();

            $row[] = '<div class="prop-title">' . $category->name . '</div>'
                    . '<div>' . $category->recipients . '</div>';
            $row['context-menu'] = $category_handler->buildBackendLinks($category);

            $list_body[] = $row;

        endforeach;

        // Prepare output
        $output[] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/contact/categories/create') . '" class="k-button">Create</a>'
            ),
        );
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $category) {
        
        // Load dependencies
        $category_handler = \Natty::getHandler('contact--category');

        // Create
        if ( 'create' == $mode ) {
            $category = $category_handler->create();
        }
        // Edit
        else {
            \Natty::getResponse()->attribute('title', 'Edit ' . $category->name);
        }

        $bounce_url = \Natty::url('backend/contact/categories');

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'contact-category-form',
        ));
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $category->name,
            'maxlength' => 128,
            'required' => TRUE,
        );
        $form->items['basic']['_data']['recipients'] = array (
            '_label' => 'Recipients',
            '_widget' => 'textarea',
            '_default' => $category->recipients,
            '_description' => 'Enter a comma-separated list of recipients.',
            'required' => 1,
        );
        $form->items['basic']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled'
            ),
            '_default' => $category->status,
            'class' => array ('options-inline')
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

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            $category->setState($form_values);
            $category->save();

            $form->redirect = $bounce_url;
            $form->onProcess();

        endif;

        // Prepare response
        $output[] = $form->getRarray();
        return $output;
        
    }
    
}