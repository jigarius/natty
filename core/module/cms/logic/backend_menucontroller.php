<?php

namespace Module\Cms\Logic;

class Backend_MenuController {
    
    public static function pageManage() {

        // Load dependencies
        $menu_handler = \Natty::getHandler('cms--menu');

        // List head
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => '', 'class' => array ('context-menu'))
        );
        $menu_coll = $menu_handler->read();

        // List body
        $list_body = array ();
        foreach ( $menu_coll as $menu ):
            
            $row = array ();

            $row['name'] = array (
                '<div class="prop-title">' . $menu->name . ($menu->isLocked ? ' (Locked)' : '') . '</div>',
                '<div class="prop-description">Internal name: ' . $menu->mcode . '</div>',
            );

            // Action links
            $row['context-menu'] = $menu->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/cms/menu/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $menu) {
        
        $menu_handler = \Natty::getHandler('cms--menu');

        if ( 'edit' == $mode ) {
            if ( $menu->isLocked )
                \Natty::error(403);
        }
        else {
            $menu = $menu_handler->create(array (
                'isNew' => TRUE
            ));
        }

        $bounce_url = \Natty::url('backend/cms/menu');

        // Build form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-menu-form'
        ));
        $form->items['default']['_label'] = 'Basic Data';
        $form->items['default']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            '_description' => 'Will be displayed in the administrative section.',
            'required' => TRUE,
            '_default' => $menu->name,
        );
        $form->items['default']['_data']['mcode'] = array (
            '_widget' => 'iname',
            '_default' => $menu->mcode,
            '_iname' => array (
                'base' => 'name',
            ),
        );
        if ( 'edit' == $mode )
            $form->items['default']['_data']['mcode']['disabled'] = 'disabled';

        $form->actions['submit'] = array (
            '_type' => 'submit',
            '_label' => 'Save'
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url
        );
        $form->onPrepare();

        // Handle validation
        if ( $form->isSubmitted() ):

            $form_values = $form->getValues();

            if ( $menu->mcode != $form_values['mcode'] ):
                $conflict = $menu_handler->read(array (
                    'key' => array ('mcode' => $form_values['mcode']),
                ));
                if ( $conflict ):
                    $form->isValid(FALSE);
                    $form->items['default']['_data']['mcode']['_errors'][] = 'The internal name is already in use.';
                endif;
            endif;

            $form->onValidate();

        endif;

        // Process data
        if ( $form->isValid() ):

            $form_data = $form->getValues();

            $menu->setState($form_data);
            $menu->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->redirect($bounce_url);

        endif;

        // Prepare response
        return $form->getRarray();
        
    }
    
    public static function pageAction() {
        
        $request = \Natty::getRequest();
        $response = \Natty::getResponse();
        $menu = $request->getEntity('with', 'cms--menu');
        
        switch( $request->getString('do') ):
            case 'delete':
                
                if ( !$menu )
                    \Natty::error(400);
                
                $menu->delete();
                
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                
                break;
        endswitch;
        
        $response->bounce();
        
    }
    
}