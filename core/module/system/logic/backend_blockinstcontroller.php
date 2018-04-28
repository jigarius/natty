<?php

namespace Module\System\Logic;

class Backend_BlockInstController {
    
    public static function pageManage($skin_code) {
        
        // Determine skin
        if ( !$skin = \Natty::getSkin($skin_code) )
            return \Natty::error(400);

        // Determine positions
        $skin_positions = array_merge(array (
            '' => 'Disabled',
        ), $skin->positions);

        // Prepare position-wise data
        $position_data = array ();
        foreach ( $skin_positions as $position_code => $position_name ):
            $position_data[$position_code] = array (
                'name' => $position_name,
                'rows' => array (),
            );
        endforeach;
        unset ($position_code, $position_name);

        // Un-assigned position displays last
        $position_unassigned = array_shift($position_data);
        $position_data[''] = $position_unassigned;
        unset ($position_unassigned);

        // Read existing block instances
        $blockinst_handler = \Natty::getHandler('system--blockinst');
        $blockinst_coll = $blockinst_handler->read();

        // All blocks must be assigned to valid positions
        foreach ( $blockinst_coll as $blockinst ):

            $blockinst_updated = FALSE;

            // Create visibility data for this skin if not available
            if ( !isset ($blockinst->visibility[$skin_code]) ) {
                $blockinst_updated = TRUE;
            }
            // Validate positioning data
            else {
                $blockinst_position = $blockinst->visibility[$skin_code]['position'];
                if ( !isset ($skin_positions[$blockinst_position]) ):

                    \Natty\Console::message('Block "' . $blockinst->description . '" was assigned to an invalid position and has been un-assigned.');

                    $blockinst->visibility[$skin_code]['position'] = '';
                    $blockinst_updated = TRUE;

                endif;
            }

            // If changes were made, save the instance
            if ( $blockinst_updated )
                $blockinst->save();

            // Prepare block instance for display
            $blockinst_position = (string) $blockinst->visibility[$skin_code]['position'];

            $row = array (
                '_ooa' => $blockinst->visibility[$skin_code]['ooa'],
                '_data' => array (
                    '<div class="form-item"><input type="number" name="items[' . $blockinst->biid . '][ooa]" value="' . $blockinst->visibility[$skin_code]['ooa'] . '" size="4" class="prop-ooa n-ta-ri" /></div>',
                    '<div class="prop-title">' . $blockinst->description . '</div>',
                    array (
                        '_render' => 'form_item',
                        '_widget' => 'dropdown',
                        '_options' => $skin_positions,
                        '_default' => $blockinst->visibility[$skin_code]['position'],
                        'name' => 'items.' . $blockinst->biid . '.position',
                        'class' => array ('prop-position'),

                    ),
                    'context-menu' => $blockinst->call('buildBackendLinks', array (
                        'skinCode' => $skin_code,
                    )),
                ),
            );

            $position_data[$blockinst_position]['rows'][] = $row;

        endforeach;

        // Handle form submission
        if ( isset ($_POST['submit']) ):

            $form_data = $_POST['items'];
            foreach ( $blockinst_coll as $biid => $blockinst ):

                if ( !isset ($form_data[$biid]) )
                    continue;

                $blockinst->visibility[$skin_code]['position'] = $form_data[$biid]['position'];
                $blockinst->visibility[$skin_code]['ooa'] = (int) $form_data[$biid]['ooa'];

                $blockinst->save();

            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        // List installed blocks
        $list_body = array ();

        foreach ( $position_data as $position_code => $position ):

            // Position heading
            $row = array (
                array (
                    'class' => array ('th', 'system-ooa'),
                    '_data' => '',
                ),
                array (
                    'class' => array ('th'),
                    '_data' => $position['name']
                            . '<input type="hidden" class="prop-position" value="' . $position_code . '" />',
                ),
                array (
                    '_data' => 'Position',
                    'class' => array ('th'),
                    'width' => 200,
                ),
                array (
                    'class' => array ('th', 'context-menu'),
                    '_data' => ''
                ),
            );
            $list_body[] = $row;

            // Position blocks
            if ( sizeof($position['rows']) ):

                // Sort items
                usort($position['rows'], 'natty_compare_ooa');

                foreach ( $position['rows'] as $row ):
                    $list_body[] = $row;
                endforeach;

            endif;

        endforeach;

        // List form
        $list_form = array (
            '_actions' => array (
                '<input name="submit" type="submit" class="k-button k-primary" value="Save" />',
                '<a href="' . \Natty::url('backend/system/block-inst') . '" class="k-button">Back</a>'
            ),
        );

        // Prepare document
        \Natty::getResponse()->attribute('title', natty_text('[@skin]: Blocks', array ('skin' => $skin->name)));
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => '<a href="' . \Natty::url('backend/system/block-inst/' . $skin_code . '/create') . '" class="k-button">Add Block</a>'
        );
        $output['table'] = array (
            '_render' => 'table',
            '_body' => $list_body,
            '_form' => $list_form,
        );
        return $output;
        
    }
    
    public static function pageCreate($skin_code) {
        
        // Determine skin
        if ( !$skin = \Natty::getSkin($skin_code) )
            return \Natty::error(400);

        // Read block types
        $block_coll = \Module\System\Controller::readBlocks();

        // Build a list
        $list_body = array ();
        foreach ( $block_coll as $bid => $block ):

            $block = (object) $block;

            $row = array ();
            $row[] = '<div class="prop-title">' . $block->name . '</div>'
                . ($block->description ? '<div class="prop-description">' . $block->description . '</div>' : '');

            $row['system-action'] = '<a href="' . \Natty::url('backend/system/block-inst/' . $skin_code . '/create/' . $bid) . '" class="k-button">Proceed</a>';

            $list_body[] = $row;

        endforeach;

        // Prepare output
        \Natty::getResponse()->attribute('title', natty_text('[@skin]: Add block', array ('skin' => $skin->name)));
        $output = array ();
        $output[] = '<div class="n-blocktext">Choose the type of block which you wish to display.</div>';
        $output['table'] = array (
            '_render' => 'table',
            '_head' => array (
                array ('_data' => 'Block Type'),
                array ('_data' => '', 'class' => array ('context-menu')),
            ),
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $binst, $skin_code_default, $bid) {
        
        // Frontend and backend skins
        $fe_skin = \Natty::readSetting('system--frontendSkin', NATTY_SKIN_DEFAULT);
        $be_skin = \Natty::readSetting('system--backendSkin', NATTY_SKIN_DEFAULT);

        // Load dependencies
        $block_coll = \Module\System\Controller::readBlocks();
        $binst_handler = \Natty::getHandler('system--blockinst');
        $package_handler = \Natty::getHandler('system--package');

        // Load active skins
        $skin_coll = $package_handler->read(array (
            'key' => array ('type' => 'skin', 'status' => 1),
            'ordering' => array ('{code} = :feSkin' => 'desc'),
            'parameters' => array (
                'feSkin' => $fe_skin,
            ),
        ));

        // Create
        if ( 'create' == $mode ) {

            // Load block type
            if ( !isset ($block_coll[$bid]) )
                return \Natty::error(400);
            $block = $block_coll[$bid];

            // Prepare instance
            $binst = $binst_handler->create(array (
                'bid' => $bid,
            ));

            \Natty::getResponse()->attribute('title', 'Create Block');

        }
        // Modify
        else {

            // Load block type
            if ( !isset ($block_coll[$binst->bid]) )
                return \Natty::error(500);
            $block = $block_coll[$binst->bid];

            \Natty::getResponse()->attribute('title', 'Edit Block');

        }

        // Load skin
        if ( !$skin = \Natty::getSkin($skin_code_default) )
            return \Natty::error(400);

        // Load providing module
        if ( !$mod_provider = \Natty::getPackage('module', $block['module']) )
            return \Natty::error(500);

        // Bounce URL
        $bounce_url = \Natty::url('backend/system/block-inst/' . $skin_code_default);

        // Build a form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-block-form',
        ), array (
            'entity' => $binst,
        ));

        // Add custom callback
        $form->addListener(array ($block['helper'], 'handleSettingsForm'));

        $form->items['default'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
            '_data' => array (
                'bid' => array (
                    '_widget' => 'markup',
                    '_label' => 'Block Type',
                    '_markup' => $block['name'],
                    '_ignore' => TRUE,
                    'readonly' => TRUE,
                ),
                'heading' => array (
                    '_widget' => 'input',
                    '_label' => 'Heading',
                    '_type' => 'text',
                    '_default' => $binst->heading,
                    '_description' => 'Leave blank if you do not want a block heading.',
                    'maxlength' => 255,
                ),
                'description' => array (
                    '_widget' => 'input',
                    '_label' => 'Description',
                    '_type' => 'text',
                    '_default' => $binst->description,
                    '_description' => 'An administrative label for the block.',
                    'maxlength' => 255,
                    'required' => 1,
                ),
            ),
        );

        // Additional settings
        $form->items['settings'] = array (
            '_widget' => 'container',
            '_label' => 'Settings',
            '_data' => array (),
        );
        $form->items['settings']['_data']['settings.cssClass'] = array (
            '_widget' => 'input',
            '_label' => 'CSS classes',
            '_default' => $binst->settings['cssClass'],
            '_description' => 'Leave blank if you don\'t know what it means.',
            'maxlength' => 255,
        );

        // Show visibility options for all enabled skins
        $form->items['visibility'] = array (
            '_widget' => 'container',
            '_label' => 'Positioning',
            '_data' => array (),
        );
        foreach ( $skin_coll as $skin ):

            $skin_code = $skin->code;

            // Create defaults
            if ( !isset ($binst->visibility[$skin_code]) )
                $binst->visibility[$skin_code] = array ();

            $binst->visibility[$skin_code] = array_merge(array (
                'position' => '',
                'ooa' => '',
            ), $binst->visibility[$skin_code]);

            $item_hint = FALSE;
            if ( $fe_skin === $skin->code && $be_skin == $skin->code )
                $item_hint = 'This is the default front-end and back-end skin.';
            elseif ( $fe_skin === $skin->code )
                $item_hint = 'This is the default front-end skin.';
            elseif ( $be_skin === $skin->code )
                $item_hint = 'This is the default back-end skin.';

            $form->items['visibility']['_data']['visibility.' . $skin_code . '.position'] = array (
                '_widget' => 'dropdown',
                '_label' => $skin->name,
                '_options' => $skin->positions,
                '_default' => $binst->visibility[$skin_code]['position'],
                '_description' => $item_hint,
                'placeholder' => 'Disabled',
            );

        endforeach;

        // Status and exceptions
        $form->items['status'] = array (
            '_widget' => 'container',
            '_label' => 'Status Options',
            '_data' => array (),
        );
        $form->items['status']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_options' => array (
                1 => 'Visible on all pages, except the ones mentioned below.',
                0 => 'Visible only on the pages mentioned below.',
            ),
            '_default' => $binst->status,
        );
        $form->items['status']['_data']['statusExceptions'] = array (
            '_widget' => 'textarea',
            '_label' => 'Exceptions',
            '_description' => 'Enter one path or pattern per line, like <strong>page/legal/privacy-policy</strong> or <strong>page/legal/%</strong>',
            '_default' => implode("\n", $binst->statusExceptions),
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
            'type' => 'submit'
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

            $form_data = $form->getValues();

            // Process status options
            $form_data['statusExceptions'] = trim($form_data['statusExceptions']);
            if ( strlen($form_data['statusExceptions']) > 0 )
                $form_data['statusExceptions'] = explode("\n", $form_data['statusExceptions']);
            else
                $form_data['statusExceptions'] = array ();

            // Merge visibility data with existing data
            $binst->visibility = natty_array_merge_nested($binst->visibility, $form_data['visibility']);
            unset ($form_data['visibility']);

            $binst->setState($form_data);
            $binst->save();

            $form->redirect = $bounce_url;
            $form->onProcess();

            if ( $form->isValid() )
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

        endif;

        // Prepare document
        $output = $form->getRarray();
        return $output;
        
    }
    
    public static function pageAction() {
        
        $request = \Natty::getRequest();

        if ( !$action = $request->getString('do') )
            \Natty::error(500);

        // Read block instance
        $entity = FALSE;
        if ( $with = $request->getString('with') ):
            $entity = \Natty::getEntity('system--blockinst', $with);
            if ( !$entity )
                \Natty::error(400);
        endif;

        // Read skin, if specified
        $skin = FALSE;
        if ( $skin_code = $request->getString('skin-code') ):
            $skin = \Natty::getSkin($skin_code);
            if ( !$skin )
                \Natty::error(400);
        endif;

        // Do as specified
        switch ( $action ):
            case 'delete':
                if ( !$skin ) {
                    \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                    $entity->delete();
                }
                if ( $skin_code && isset ($entity->visibility[$skin_code]) ) {
                    \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                    $entity->visibility[$skin_code]['position'] = '';
                    $entity->save();
                }
                break;
        endswitch;

        \Natty::getResponse()->bounce();
        
    }
    
}