<?php

namespace Module\Eav\Logic;

class Backend_AttrInstController {
    
    public static function pageManage($etid, $egid, $list_path) {
        
        // Load dependencies
        $etype_handler = \Natty::getHandler($etid);
        $attribute_handler = \Natty::getHandler('eav--attribute');
        $attrinst_handler = \Natty::getHandler('eav--attrinst');
        $datatype_handler = \Natty::getHandler('eav--datatype');

        // Read entities for ready reference
        $opts_datatype = $datatype_handler->readOptions(array (
            'ordering' => array ('name' => 'asc')
        ));
        $imethod_coll = \Module\Eav\Controller::readInputMethods();

        // Bounce-back URI
        $bounce = \Natty::getRequest()->getUri();

        // List headings
        $list_head = array (
            array ('_data' => 'OOA', 'class' => array ('system-ooa')),
            array ('_data' => 'Internal Name'),
            array ('_data' => 'Label'),
            array ('_data' => 'Data Type'),
            array ('_data' => 'Input Method'),
            array ('_data' => '', 'class' => array ('context-menu'))
        );

        // List existing instances
        $used_attrinst_coll = $attribute_handler->readAttributeInstances($etid, $egid);

        $list_body = array ();
        foreach ( $used_attrinst_coll as $aiid => $attrinst ):

            // Datatype for this attribute
            $datatype_name = $opts_datatype[$attrinst->dtid]['_data'];

            // Determine input method options
            $fitem_imethod = array (
                '_render' => 'form_item',
                '_widget' => 'dropdown',
                '_options' => array (),
                'name' => 'items[' . $aiid . '][imid]',
            );

            foreach ( $imethod_coll as $imid => $imethod ):
                if ( in_array($attrinst->dtid, $imethod['datatypes']) ):
                    $fitem_imethod['_options'][$imid] = $imethod['name'];
                endif;
            endforeach;

            $row = array (
                array (
                    '_data' => '<div class="form-item"><input type="number" name="items[' . $aiid . '][ooa]" value="' . $attrinst->ooa . '" class="n-ta-ri prop-id" /></div>'
                        .'<input type="hidden" name="items[' . $aiid . '][aiid]" value="' . $attrinst->aiid . '" class="prop-id" />',
                    'class' => array ('system-ooa'),
                ),
                $attrinst->acode,
                $attrinst->name,
                $datatype_name,
                $fitem_imethod,
                'context-menu' => array (),
            );

            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attribute/' . $attrinst->aid, array (
                'bounce' => $bounce,
            )) . '">Configure Attribute</a>';
            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attr-inst/' . $attrinst->aiid, array (
                'bounce' => $bounce,
            )) . '">Edit</a>';
            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attr-inst/' . $attrinst->aiid . '/delete') . '" data-ui-init="confirmation">Delete</a>';

            $list_body[] = $row;

            unset ($datatype_name);

        endforeach;

        // Create new attr-conf
        $list_body[] = array (
            array (
                '_data' => '<div class="form-item"><input type="number" name="new[ooa]" maxlength="10" class="n-ta-ri prop-ooa" /></div>',
                'class' => array ('system-ooa'),
            ),
            '<div class="form-item"><input type="text" name="new[acode]" maxlength="32"/></div>',
            '<div class="form-item"><input type="text" name="new[name]" placeholder="Add a label" maxlength=""/></div>',
            array (
                '_data' => array (
                    '_render' => 'form_item',
                    '_widget' => 'dropdown',
                    '_options' => $opts_datatype,
                    'name' => 'new.dtid',
                    'placeholder' => '',
                ),
            ),
            '',
            '',
        );

        // Determine unassigned attribute instances
        $assigned_attr_acodes = array ();
        foreach ( $used_attrinst_coll as $attrinst ):
            $assigned_attr_acodes[] = $attrinst->acode;
        endforeach;
        $attrinst_options = $attribute_handler->readOptions(array (
            'conditions' => array (
                array ('AND', array ('status', '=', 1)),
                array ('AND', array ('acode', 'NOT IN', $assigned_attr_acodes))
            ),
            'ordering' => array ('acode' => 'asc')
        ));

        // Show an option to assign unused attribute instances
        if ( sizeof($attrinst_options) ):
            $list_body[] = array (
                array (
                    '_data' => '<div class="form-item"><input type="number" name="existing[ooa]" maxlength="10" class="n-ta-ri prop-ooa" /></div>',
                    'class' => array ('system-ooa'),
                ),
                array (
                    '_data' => array (
                        '_render' => 'form_item',
                        '_widget' => 'dropdown',
                        '_options' => $attrinst_options,
                        'name' => 'existing.aid',
                        'placeholder' => 'Assign existing attribute',
                    ),
                    'valign' => 'top',
                ),
                '<div class="form-item"><input type="text" name="existing[name]" placeholder="Add a label" maxlength="128"/></div>',
                '',
                '',
                '',
            );
        endif;

        // Handle form submission
        if ( isset ($_POST['submit']) ):

            $form_valid = TRUE;
            $form_redirect = NULL;

            // Attach an unattached attribute
            if ( isset ($_POST['existing']) && $_POST['existing']['aid'] ):

                $form_data = $_POST['existing'];

                $attribute = $attribute_handler->readById($form_data['aid']);
                if ( !$attribute ):
                    $form_valid = FALSE;
                    \Natty::error(400);
                endif;

                if ( !$form_data['name'] )
                    $form_data['name'] = $attribute->name;

                $form_data['aid'] = $attribute->aid;
                $form_data['etid'] = $etid;
                $form_data['egid'] = $egid;

                $conflict = $attrinst_handler->readByKeys(array (
                    'aid' => $attribute->aid,
                    'etid' => $etid,
                    'egid' => $egid,
                    'status' => 1,
                ), array (
                    'nocache' => 1
                ));

                if ( $conflict ):
                    \Natty\Console::error('Attribute is already assigned.');
                    $form_valid = FALSE;
                endif;

                if ( $form_valid ) {

                    $attrinst = $attrinst_handler->create($form_data);
                    $attrinst->save();

                    $form_redirect = \Natty::url('backend/eav/attr-inst/' . $attrinst->aiid, array (
                        'bounce' => TRUE,
                    ));

                }
                else {
                    \Natty::getResponse()->refresh();
                }

            endif;

            // Generate and attach a new attribute
            if ( $_POST['new']['name'] ):

                $form_data = $_POST['new'];

                // Determine attribute code
                $form_data['acode'] = $form_data['acode']
                        ? $form_data['acode'] : $form_data['name'];
                $form_data['acode'] = 'eav' . natty_strtocamel($form_data['acode'], TRUE);

                // Validate Data Type ID
                if ( !$form_data['dtid'] ):
                    \Natty\Console::error('Please select a data type for the attribute.');
                    $form_valid = FALSE;
                endif;

                // Validate acode
                if ( !$form_data['acode'] ) {
                    \Natty\Console::error('Please choose a proper label and code for the attribute.');
                    $form_valid = FALSE;
                }
                else {
                    // Check for conflict
                    $conflict = $attribute_handler->readByKeys(array (
                        'acode' => $form_data['acode'],
                        'status' => 1
                    ));
                    if ( $conflict ):
                        \Natty\Console::error('An attribute with the given code already exists.');
                        $form_valid = FALSE;
                    endif;
                }

                if ( $form_valid ) {

                    $attr = $attribute_handler->create($form_data);
                    $attr->module = 'eav';
                    $attr->save();

                    $attrinst = $attrinst_handler->create(array (
                        'aid' => $attr->aid,
                        'acode' => $attr->acode,
                        'etid' => $etid,
                        'egid' => $egid,
                        'name' => $attr->name,
                    ));
                    $attrinst->save();

                    $attrinst_url = \Natty::url('backend/eav/attr-inst/' . $attrinst->aiid, array (
                        'bounce' => TRUE,
                    ));

                    $form_redirect = \Natty::url('backend/eav/attribute/' . $attr->aid, array (
                        'also-update' => $attrinst->aiid,
                        'continue' => $attrinst_url,
                    ));

                }
                else {
                    \Natty::getResponse()->refresh();
                }

            endif;

            // Update ordering info
            foreach ( $used_attrinst_coll as $attrinst ):

                if ( !isset ( $_POST['items'][$attrinst->acode] ) )
                    continue;

                $attrinst->ooa = (int) $_POST['items'][$attrinst->acode]['ooa'];
                $attrinst->settings['input']['imid'] = (int) $_POST['items'][$attrinst->acode]['imid'];

                $attrinst->save();

            endforeach;

            // Show success message and redirect
            if ( $form_valid ):
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                if ( $form_redirect )
                    \Natty::getResponse()->redirect($form_redirect);
                else
                    \Natty::getResponse()->refresh();
            endif;

        endif;

        // List form
        $list_form = array (
            'method' => 'post',
            'action' => '',
            '_actions' => array (
                '<input name="submit" type="submit" value="Save" class="k-button k-primary" />',
                '<a href="' . \Natty::url($list_path) . '" class="k-button">Back</a>',
            ),
        );

        // Prepare output
        $output = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            '_form' => $list_form,
        //    'data-ui-init' => array ('sortable'),
        );

        // Add the sortable plugin
        $response = \Natty::getResponse();
        $response->addScript(\Natty::path('core/plugin/natty/natty.sortable.js'));
        
        return $output;
        
    }
    
    public static function pageForm($mode, $attrinst, $attribute) {
        
        if ( !$attribute )
            $attribute = \Natty::getEntity('eav--attribute', $attrinst->aid);
        $etype = \Natty::getEntity('system--entitytype', $attrinst->etid);

        $egroup_data = \Natty::getHandler($attrinst->etid)->getEntityGroupData();
        $egroup = FALSE;
        if ( isset ($egroup_data[$attrinst->egid]) )
            $egroup = $egroup_data[$attrinst->egid];

        $crud_helper = $attrinst->crudHelper;
        $imethod_helper = FALSE;
        if ( $attrinst->settings['input']['method'] )
            $imethod_helper = $crud_helper::getInputMethodHelper($attrinst->settings['input']['method']);

        // The Bounce-back URI
        $bounce_url = \Natty::getRequest()->getString('bounce');

        // Attribute instance must not be locked
        if ( $attrinst->isLocked )
            \Natty::error(400);

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'eav-ainst-form'
        ), array (
            'attribute' => $attrinst,
        ));

        $form->addListener(array ($crud_helper, 'handleSettingsForm'));
        if ( $imethod_helper )
            $form->addListener(array ($imethod_helper, 'handleSettingsForm'));

        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Label',
            '_default' => $attrinst->name,
            'required' => TRUE,
        );
        $form->items['basic']['_data']['acode'] = array (
            '_widget' => 'input',
            '_label' => 'Internal Name',
            '_default' => $attrinst->acode,
            '_ignore' => TRUE,
            'readonly' => TRUE,
        );
        $form->items['basic']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_description' => 'Input instructions for this attribute. This would be displayed in the input form.',
            '_default' => $attrinst->description,
        );
        $form->items['basic']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled'
            ),
            '_default' => $attrinst->status,
            'class' => array ('options-inline'),
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
            'type' => 'submit'
        );
        if ( $bounce_url ):
            $form->actions['back'] = array (
                '_label' => 'Back',
                'type' => 'anchor',
                'href' => $bounce_url
            );
        endif;

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form_values = $form->getValues();

            // Validate number of values
            if ( $attribute->settings['input']['nov'] > 0 ):
                if ( $form_values['settings']['input']['nov'] > $attribute->settings['input']['nov'] ):
                    $form->items['general']['_data']['settings.input.nov']['_errors'][] = 'As per global settings, value should be less than ' . $attribute->settings['input']['nov'] . '.';
                endif;
            endif;

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();

            // Merge with default input settings
            $attrinst->settings['input'] = array_merge($attrinst->settings['input'], $form_values['settings']['input']);
            if ( isset ($form_values['settings']['storage']) )
                $attrinst->settings['storage'] = natty_array_merge_nested($attrinst->settings['storage'], $form_values['settings']['storage']);
            unset ($form_values['settings']);

            $attrinst->setState($form_values);
            $attrinst->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            $form->redirect = $bounce_url;

            $form->onProcess();

        endif;

        // Prepare output
        $page_heading = 'Edit: ' . $attribute->name . ' for ' . $egroup['name'];

        \Natty::getResponse()->attribute('title', $page_heading);
        $output[] = array (
            '_render' => 'markup',
            '_markup' => '<div class="n-blocktext">These are specific configurations for this attribute instance.</div>'
        );
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
    public static function actionDelete($attrinst) {
        
        if ( $attrinst->status <= 0 )
            \Natty::error(400);
        
        $attrinst->status = -1;
        $attrinst->save();
        
        \Natty\Console::success();
        \Natty::getResponse()->bounce();
        
    }
    
}