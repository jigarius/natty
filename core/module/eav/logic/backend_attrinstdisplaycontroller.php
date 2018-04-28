<?php

namespace Module\Eav\Logic;

class Backend_AttrInstDisplayController {
    
    public static function pageManage($etid, $egid, $view_mode, $egroup_url) {
        
        // Read all attributes attached to the entity group
        $attr_handler = \Natty::getHandler('eav--attribute');
        $attr_data = $attr_handler->readAttributeInstances($etid, $egid, array (
            'viewMode' => $view_mode
        ));

        // Build a table
        $list_head = array (
            array ('_data' => 'OOA', 'width' => '50'),
            array ('_data' => 'Attribute'),
            array ('_data' => 'Label', 'width' => '100'),
            array ('_data' => 'Display', 'width' => '100'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        $list_body = array (
            'visible' => array (),
            'hidden' => array (),
        );

        $omethod_coll = \Module\Eav\Controller::readOutputMethods();
        foreach ( $attr_data as $acode => $o_attrinst ):

            $output_settings = isset ($o_attrinst->settings['output'][$view_mode])
                    ? $o_attrinst->settings['output'][$view_mode] : $o_attrinst->settings['output']['default'];

            // Determine label options
            $fitem_label = array (
                '_render' => 'form_item',
                '_widget' => 'dropdown',
                '_options' => array (
                    'hidden' => 'Hidden',
                    'inline' => 'Inline',
                    'above' => 'Above',
                ),
                '_default' => $output_settings['label'],
                'name' => 'items.' . $acode . '.label',
            );

            // Determine output methods
            $fitem_method = array (
                '_render' => 'form_item',
                '_widget' => 'dropdown',
                '_options' => array (),
                '_default' => $output_settings['method'],
                'placeholder' => 'Hidden',
                'name' => 'items.' . $acode . '.method',
            );

            foreach ( $omethod_coll as $omid => $output_method ):
                if ( !in_array($o_attrinst->dtid, $output_method['datatypes']) )
                    continue;
                $fitem_method['_options'][$omid] = $output_method['name'];
            endforeach;

            $row = array ();

            $row[] = array (
                '_data' => '<div class="form-item"><input name="items[' . $acode . '][ooa]" type="number" value="' . $output_settings['ooa'] . '" class="prop-ooa n-ta-ri" /></div>',
            );
            $row[] = $o_attrinst->name;
            $row[] = $fitem_label;
            $row[] = $fitem_method;

            $row['context-menu'] = array ();
            $row['context-menu'][] = '<a href="' . \Natty::url('backend/eav/attr-inst/' . $o_attrinst->aiid . '/display/' . $view_mode, array (
                'bounce' => TRUE,
            )) . '">Configure</a>';

            if ( 'hidden' == $output_settings['method'] )
                $list_body['visible'][] = $row;
            else
                $list_body['hidden'][] = $row;

        endforeach;
        $list_body = array_merge($list_body['visible'], $list_body['hidden']);

        // Wrap table in form
        $r_table = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
        );
        $r_table['_form'] = array (
            'method' => 'post',
            'action' => '',
            '_actions' => array (
                '<input type="submit" name="submit[save]" value="Save" class="k-button k-primary" />',
                '<a href="' . \Natty::url($egroup_url) . '" class="k-button">Back</a>',
            ),
        );

        // Handle form submission
        if ( isset ($_POST['submit']) ):

            foreach ( $attr_data as $acode => $o_attrinst ):
                if ( isset ($_POST['items'][$acode]) ):

                    $o_attrinst->settings['output'][$view_mode]['ooa'] = (int) $_POST['items'][$acode]['ooa'];
                    $o_attrinst->settings['output'][$view_mode]['label'] = $_POST['items'][$acode]['label'];
                    $o_attrinst->settings['output'][$view_mode]['method'] = $_POST['items'][$acode]['method'];

                    $o_attrinst->save();

                endif;
            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        // Render output
        return $r_table;
        
    }
    
    public static function pageForm($attrinst, $view_mode) {
        
        // Load dependencies
        $etype = \Natty::getEntity('system--entitytype', $attrinst->etid);

        // Determine entity group
        $egroup_data = \Natty::getHandler($attrinst->etid)->getEntityGroupData();
        $egroup = FALSE;
        if ( isset ($egroup_data['groups'][$attrinst->egid]) )
            $egroup = $egroup_data['groups'][$attrinst->egid];

        // Validate view mode
        if ( !isset ($etype->viewModes[$view_mode]) )
            throw new \Natty\Core\ControllerException(400);

        $bounce_url = \Natty::getRequest()->getString('bounce');
        $crud_helper = $attrinst->crudHelper;

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'eav-attrinst-form-display',
        ));

        // Attach output settings
        $crud_helper::attachOutputSettingsForm($attrinst, $form, $view_mode);

        $form->actions['submit.save'] = array (
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

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();

            // Create and merge output settings for this view mode
            if ( !isset ($attrinst->settings['output'][$view_mode]) )
                $attrinst->settings['output'][$view_mode] = $attrinst->settings['output']['default'];
            $attrinst->settings['output'][$view_mode] = array_merge($attrinst->settings['output'][$view_mode], $form_values);

            $attrinst->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            $form->redirect = $bounce_url;

            $form->onProcess();

        endif;

        // Prepare output
        $page_heading = natty_text('Display settings: %%attrinst for %%egroup', array (
            'attrinst' => $attrinst->name,
            'egroup' => $egroup['name'],
        ));

        \Natty::getResponse()->attribute('title', $page_heading);
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
}