<?php

namespace Module\Cms\Logic;

class Backend_MenuItemController {
    
    public static function pageManage($menu) {
        
        // Read items
        $menuitem_coll = \Natty::getHandler('cms--menuitem')->read(array (
            'key' => array ('mid' => $menu->mid),
        ));

        // Validate form
        $form_submitted = isset ($_POST['save']);
        $form_errors = array ();
        if ( $form_submitted ):

            foreach ( $menuitem_coll as $menuitem ):

                if ( !isset ($_POST['items'][$menuitem->miid]) ):
                    $form_errors[] = $menuitem->name . ': Data is missing.';
                    continue;
                endif;

                $form_item = $_POST['items'][$menuitem->miid];

                if ( !is_numeric($form_item['ooa']) || !is_numeric($form_item['parentId']) || !is_numeric($form_item['ooa']) ):
                    $form_errors[] = $menuitem->name . ': Data is invalid.';
                    continue;
                endif;

                $menuitem->parentId = $form_item['parentId'];
                $menuitem->ooa = $form_item['ooa'];
                $menuitem->level = $form_item['level'];

            endforeach;

            unset ($form_item);

        endif;

        $menuitem_coll = natty_sort_tree($menuitem_coll, array (
            'idKey' => 'miid',
            'ooaKey' => 'ooa',
        ));

        // Process form
        if ( $form_submitted && !$form_errors ):

            foreach ( $_POST['items'] as $form_item ):

                $menuitem = $menuitem_coll[$form_item['miid']];
                $menuitem->save();

            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        // Prepare list
        $list_head = array (
            array ('_data' => 'OOA', 'class' => array ('system-ooa')),
            array ('_data' => 'Item'),
            array ('_data' => 'Active?', 'width' => 100),
            array ('class' => array ('context-menu'), '_data' => '&nbsp;'),
        );
        $list_body = array ();
        foreach ( $menuitem_coll as $miid => $menuitem ):

            $row = array (
                array (
                    '_data' => '<div class="form-item"><input type="number" value="' . $menuitem->ooa . '" class="n-ta-ri" /></div>'
                            .'<input type="hidden" name="items[' . $miid . '][miid]" value="' . $menuitem->miid . '" class="prop-id" />'
                            .'<input type="hidden" name="items[' . $miid . '][parentId]" value="' . $menuitem->parentId . '" class="prop-parentId" />'
                            .'<input type="hidden" name="items[' . $miid . '][level]" value="' . $menuitem->level . '" class="prop-level" />'
                            .'<input type="hidden" name="items[' . $miid . '][ooa]" value="' . $menuitem->ooa . '" class="prop-ooa" />',
                    'class' => array ('system-ooa')
                ),
                str_repeat('<span class="n-indent"></span>', $menuitem->level) . '<span class="prop-title">' . $menuitem->name . '</span>',
                $menuitem->status ? 'Yes' : 'No',
                'context-menu' => array ()
            );

            $row['context-menu'][] = '<a href="' . \Natty::url('backend/cms/menu/' . $menuitem->mid . '/items/' . $menuitem->miid) . '">Edit</a>';
            $row['context-menu'][] = '<a href="' . \Natty::url('backend/cms/menu/' . $menuitem->mid . '/items/' . $menuitem->miid . '/delete') . '">Delete</a>';

            $list_body[] = $row;

        endforeach;

        // Prepare document
        $output = array (
            array (
                '_render' => 'toolbar',
                '_right' => array (
                    '<a href="' . \Natty::url('backend/cms/menu/' . $menu->mid . '/items/create') . '" class="k-button">Create</a>'
                ),
            ),
            array (
                '_render' => 'table',
                '_head' => $list_head,
                '_body' => $list_body,
                '_form' => array (
                    '_actions' => array (
                        '<input type="submit" name="save" value="Save" class="k-button k-primary" />'
                    )
                ),
                'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
                'data-ui-init' => array ('sortable'),
            ),
        );

        $response = \Natty::getResponse();
        $response->addScript(\Natty::path('core/plugin/natty/natty.sortable.js'));
        
        return $output;
        
    }
    
    public static function pageForm($mode, $menuitem, $menu) {
        
        // Load libraries
        $menuitem_handler = \Natty::getHandler('cms--menuitem');

        // Creation mode
        if ( 'create' == $mode ) {
            $menuitem = $menuitem_handler->create(array (
                'mid' => $menu->mid,
                'ail' => \Natty::getInputLangId(),
            ));
        }
        // Editing
        else {
            
            
            
        }

        // Parent item options
        $parent_opts = $menuitem_handler->readOptions(array (
            'conditions' => array (
                array ('AND', array ('mid', '=', ':mid')),
                array ('AND', array ('menuitem.miid', '!=', ':miid'))
            ),
            'parameters' => array (
                'mid' => $menu->mid,
                'miid' => $menuitem->miid ? $menuitem->miid : 0,
                'language' => \Natty::getInputLangId(),
            ),
        ));

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-menu-item-form',
            'i18n' => TRUE,
        ));
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            '_default' => $menuitem->name,
            'maxlength' => 255,
            'required' => TRUE,
        );
        $form->items['basic']['_data']['href'] = array (
            '_widget' => 'input',
            '_label' => 'Link',
            '_default' => $menuitem->href,
            '_description' => 'Enter an internal path like <em>sign-in</em> or an absolute URL like <em>http://example.com/some/path</em>.',
        );
        $form->items['basic']['_data']['parentId'] = array (
            '_widget' => 'dropdown',
            '_label' => 'Under',
            '_default' => $menuitem->parentId,
            '_options' => $parent_opts,
            'placeholder' => ''
        );
        $form->items['basic']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_default' => $menuitem->status,
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            'class' => array ('options-inline')
        );
        $form->actions['save'] = array (
            '_label' => 'Save',
            '_type' => 'submit',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/cms/menu/' . $menu->mid . '/items')
        );
        $form->onPrepare();


        // Handle validation
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Handle processing
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            $menuitem->setState($form_values);
            $menuitem->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            $form->redirect = \Natty::url('backend/cms/menu/' . $menu->mid . '/items');

            $form->onProcess();

        endif;

        // Prepare output
        $output = $form->getRarray();
        return $output;
        
    }
    
    public static function pageAction() {
        
        $request = \Natty::getRequest();
        $response = \Natty::getResponse();
        $menuitem = $request->getEntity('with', 'cms--menuitem');
        $do = $request->getString('do');
        
        switch( $do ):
            case 'delete':
                
                if ( !$menuitem )
                    \Natty::error(400);
                
                $menuitem->delete();
                
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                
                break;
        endswitch;
        
        $response->bounce();
        
    }
    
}