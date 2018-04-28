<?php

namespace Module\Taxonomy\Logic;

class Backend_GroupController {
    
    public static function pageManage() {
        
        // Load dependencies
        $tgroup_handler = \Natty::getHandler('taxonomy--group');

        // List head
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );

        // Read data
        $query = $tgroup_handler->getQuery();
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $paging_helper->setParameters($list_head);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            )
        ));

        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $record ):

            $tgroup = $tgroup_handler->create($record);
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $tgroup->name . '</div>'
                    .'<div class="prop-description">Internal Name: ' . $tgroup->gcode . '</div>';
            
            $row['context-menu'] = $tgroup->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Prepare response
        $output[] = array (
            '_render' => 'toolbar',
            '_right' => '<a href="' . \Natty::url('backend/taxonomy/create') . '" class="k-button">Create</a>',
        );
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode = NULL, $entity = NULL) {
        
        // Load dependencies
        $tgroup_handler = \Natty::getHandler('taxonomy--group');

        // Creation
        if ( 'create' == $mode ) {
            $entity = $tgroup_handler->create();
        }
        // Modification
        else {
            \Natty::getResponse()->attribute('title', 'Edit: ' . $entity->name);
        }

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'taxonomy-group-form',
        ));
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $entity->name,
            'maxlength' => 128,
            'required' => TRUE,
        );
        $form->items['basic']['_data']['gcode'] = array (
            '_label' => 'Internal Name',
            '_widget' => 'iname',
            '_default' => $entity->gcode,
            '_iname' => array (
                'base' => 'name',
            ),
            'maxlength' => 64,
        );
        $form->items['basic']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_default' => $entity->description,
        );
        $form->items['basic']['_data']['maxLevels'] = array (
            '_label' => 'Maximum Nesting',
            '_widget' => 'input',
            '_default' => $entity->description,
            '_description' => 'The maximum level of nesting allowed for terms.',
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/taxonomy'),
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            $entity->setState($form_values);
            $entity->save();

            if ( 'create' == $mode ) {
                $form->redirect = \Natty::url('backend/taxonomy/' . $entity->gid . '/terms');
            }
            else {
                $form->redirect = \Natty::url('backend/taxonomy');
            }

            $form->onProcess();

        endif;

        // Prepare response
        $output[] = $form->getRarray();
        
        return $output;
        
    }
    
    public static function pageAction($action, $with = NULL) {
        
        $entity = FALSE;
        if ( $with )
            $entity = \Natty::getEntity('taxonomy--group', $with);
        
        switch ( $action ):
            case 'delete':
                
                if ( !$entity )
                    \Natty::error(400);
                
                $entity->delete();
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                
                break;
            default:
                \Natty\Console::error(NATTY_ACTION_UNRECOGNIZED);
                break;
        endswitch;
        
        $bounce = \Natty::url('backend/taxonomy');
        \Natty::getResponse()->bounce($bounce);
        
    }
    
}