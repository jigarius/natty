<?php

namespace Module\Eav\Logic;

class Backend_AttributeController {
    
    public static function pageCreate() {
        
        // Create a list of available data-types
        $datatype_data = \Natty::getHandler('eav--datatype')->read();

        // Build list
        $list_head = array (
            array ('_data' => 'Data Type'),
        );
        $list_body = array ();

        foreach ( $datatype_data as $datatype ):

            $row = array ();
            $row[] = '<a href="' . \Natty::url('#') . '">' . $datatype->name . '</a>'
                    . '<div class="description">' . $datatype->description . '</div>';

            $list_body[] = $row;

        endforeach;

        // Prepare response
        $output = array (
            array (
                '_render' => 'table',
                '_head' => $list_head,
                '_body' => $list_body,
                'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
            ),
        );
        return $output;
        
    }
    
    public static function pageManage() {
        
        // Load dependencies
        $attr_handler = \Natty::getHandler('eav--attribute');

        // List headings
        $list_head = array (
            array ('_data' => 'Code'),
            array ('_data' => 'Name'),
            array ('_data' => 'Data Type'),
            array ('class' => array ('context-menu'), '_data' => '')
        );

        // List existing instances
        $records = $attr_handler->readByKeys(array (
            'status' => 1
        ), array (
            'ordering' => array (
                'name' => 'a'
            )
        ));

        $list_data = array ();
        foreach ( $records as $attr ):

            $row = array (
                $attr->acode,
                $attr->name . ($attr->isLocked ? ' (Locked)' : ''),
                $attr->dtid,
                'context-menu' => array (),
            );

            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attribute/' . $attr->aid) . '">Configure</a>';
            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attribute/' . $attr->aid . '/delete') . '">Delete</a>';

            $list_data[] = $row;

        endforeach;

        // Prepare output
        $output = array (
            array (
                '_render' => 'toolbar',
                '_right' => array (
                    '<a href="' . \Natty::url('backend/eav/attribute/create') . '" class="k-button">Create</a>'
                ),
            ),
            array (
                '_render' => 'table',
                '_head' => $list_head,
                '_body' => $list_data,
                'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
            )
        );
        return $output;
        
    }
    
    public static function pageForm($mode, $attribute, $datatype) {
        
        // Load dependencies
        $request = \Natty::getRequest();

        // Create
        if ( 'create' == $mode ) {
            \Natty::getResponse()->attribute('title', 'Create attribute');
        }
        // Edit
        else {
            $datatype = \Natty::getEntity('eav--datatype', $attribute->dtid);
            \Natty::getResponse()->attribute('title', 'Edit: ' . $attribute->acode);
        }

        $crud_helper = \Module\Eav\Classes\AttributeHandler::getCrudHelper($attribute->dtid);

        // Bounce URL
        $bounce_url = $request->getString('bounce');
        if ( !$bounce_url )
            $bounce_url = \Natty::url('backend/eav/attribute');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'eav-attribute-form',
        ), array (
            'etid' => 'eav--attribute',
            'entity' => &$attribute,
        ));

        $form->addListener(array ($crud_helper, 'handleSettingsForm'));

        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_label' => 'Label',
            '_widget' => 'input',
            '_default' => $attribute->name,
        );
        $form->items['basic']['_data']['acode'] = array (
            '_label' => 'Code',
            '_widget' => 'iname',
            '_default' => $attribute->acode,
            '_ignore' => TRUE,
            'readonly' => 'readonly',
        );
        $form->items['basic']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_description' => 'Description and input instructions.',
            '_default' => $attribute->description,
        );
        $form->items['basic']['_data']['isConfigured'] = array (
            '_display' => 0,
            '_widget' => 'input',
            '_value' => 1,
            'type' => 'hidden',
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
        if ( $continue_url = $request->getString('continue') ):
            $form->actions['continue'] = array (
                '_label' => 'Continue',
                'type' => 'anchor',
                'href' => $continue_url,
            );
        endif;

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();

            $attribute->settings['input'] = natty_array_merge_nested($attribute->settings['input'], $form_values['settings']['input']);
            if ( isset ($form_values['settings']['storage']) )
                $attribute->settings['storage'] = natty_array_merge_nested($attribute->settings['storage'], $form_values['settings']['storage']);
            unset ($form_values['settings']);

            $attribute->setState($form_values);
            $attribute->save();

            // Also update attribute instance
            if ( $attrinst = $request->getEntity('also-update', 'eav--attrinst') ):
                $attrinst->name = $attribute->name;
                $attrinst->description = $attribute->description;
                $attrinst->isConfigured = 1;
                $attrinst->settings['input'] = $attribute->settings['input'];
                $attrinst->save();
            endif;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

            $form_redirect = $continue_url
                    ? $continue_url : $bounce_url;
            $form->redirect = $form_redirect;

            $form->onProcess();

        endif;

        // Show non-configuration warning
        if ( !$attribute->isConfigured )
            \Natty\Console::message('Please complete initial configuration and save this form to activate this attribute.');

        // Prepare document
        $output[] = array (
            '_render' => 'markup',
            '_markup' => '<div class="n-blocktext">These are global configurations and defaults for new instances of this attribute.</div>'
        );
        $output[] = $form->getRarray();
        return $output;
        
    }
    
}