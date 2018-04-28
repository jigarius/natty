<?php

namespace Module\People\Logic;

class Backend_RoleController {
    
    public static function pageManage() {
        
        $user = \Natty::getUser(TRUE);
        $role_handler = \Natty::getHandler('people--role');

        // Prepare list object
        $list_data = $role_handler->read(array (
            'conditions' => '1=1',
        ));

        // Prepare headings
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => 'Active', 'class' => array ('size-small')),
            array ('_data' => '', 'class' => array ('context-menu')),
        );

        // Prepare rows
        $list_body = [];
        foreach ( $list_data as $key => $item ):

            $row = array ();

            $row['name'] = '<span class="prop-title">' 
                        . $item->name . ($item->isLocked ? ' (Locked)' : '')
                    . '</span>';
            $row['status'] = $item->status ? 'Yes' : 'No';

            $row['context-menu'] = $item->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/people/role/create') . '" class="k-button">Create</a>'
            )
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer')
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $role) {
        
        // Load dependencies
        $response = \Natty::getResponse();
        $role_handler = \Natty::getHandler('people--role');

        // Create
        if ( 'create' == $mode ) {
            $role = $role_handler->create();
            $response->attribute('title', 'Create role');
        }
        // Edit
        else {
            $response->attribute('title', 'Edit role');
        }
        
        // Return URL
        $bounce_url = \Natty::url('backend/people/roles');

        // Build a user form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'people-role-form'
        ));
        $form->items['default'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Information',
        );
        $form->items['default']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            '_default' => $role->name,
            'maxlength' => 32,
            'required' => TRUE
        );
        $form->items['default']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_options' => array (1 => 'Enabled', 0 => 'Disabled'),
            '_default' => $role->status,
            'class' => array ('options-inline'),
            'required' => TRUE
        );
        
        $form->actions['save'] = array (
            'type' => 'submit',
            '_label' => 'Save'
        );
        $form->actions['back'] = array (
            'type' => 'anchor',
            '_label' => 'Back',
            'href' => $bounce_url,
        );

        // Finalize form
        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            $role->setState($form_values);
            $role->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

            $form->redirect = $bounce_url;
            $form->onProcess();

        endif;

        // Prepare response
        $output = $form->getRarray();
        return $output;
        
    }
    
    public static function pagePermissions($role = NULL) {
        
        $role_handler = \Natty::getHandler('people--role');
        $response = \Natty::getResponse();
        $dbo = \Natty::getDbo();
        $tablename = '%__people_role_permission';
        
        // Read the role or roles
        if ( $role ) {
            $role_coll = array ($role->rid => $role);
            $response->attribute('title', $role->name . ': Permissions');
        }
        else {
            $role_coll = $role_handler->read();
            $response->attribute('title', 'Permissions');
        }

        // Retrieve a list of actions
        $action_coll = \Module\System\Controller::readActions();
        $module_coll = \Natty::getPackage('module');

        // Retrieve existing allotments
        $existing_perm_records = $dbo->read($tablename, array (
            'columns' => array ('rid', 'aid'),
            'conditions' => array (
                array ('AND', array ('rid', 'IN', array_keys($role_coll)))
            ),
        ));
        $existing_perm_data = array ();
        
        foreach ( $role_coll as $role ):
            $existing_perm_data[$role->rid] = array ();
        endforeach;
        
        foreach ( $existing_perm_records as $record ):
            $existing_perm_data[$record['rid']][] = $record['aid'];
        endforeach;
        
        unset ($existing_perm_records, $record);

        // List head
        $list_head = array (
            array ('_data' => 'Description'),
        );
        foreach ( $role_coll as $role ):
            $list_head[] = array (
                'class' => array ('cont-checkbox', 'n-td-rotated'),
                '_data' => '<span>' . $role->name . '</span>',
            );
        endforeach;
        
        // List body
        $list_body = array ();
        $current_package = -1;
        foreach ( $action_coll as $action ):

            // Module heading
            if ( $current_package != $action['module'] ):
                
                $row = array ();
                $row[] = '<div class="prop-title">' . $module_coll[$action['module']]->name . '</div>';
                
                foreach ( $role_coll as $role ):
                    $row[] = '&nbsp;';
                endforeach;
                
                $list_body[] = $row;
                
                $current_package = $action['module'];
                
            endif;

            // Action rows
            $row = array ();
            $row[] = $action['name'];
            
            foreach ( $role_coll as $rid => $role ):
                
                $checkbox = array (
                    '_render' => 'element',
                    '_element' => 'input',
                    'type' => 'checkbox',
                    'name' => 'items.' . $role->rid . '.',
                    'value' => $action['aid'],
                );

                // Mark already alloted actions
                if ( in_array($action['aid'], $existing_perm_data[$rid]) )
                    $checkbox['checked'] = 'checked';
                
                $row[] = array (
                    '_data' => $checkbox,
                    'class' => array ('n-ta-ce'),
                );
                
            endforeach;

            $list_body[] = $row;

        endforeach;

        // Set form actions
        $list_form = array (
            'method' => 'post',
            'action' => \Natty::getRequest()->getUri(),
            '_actions' => array (
                '<input name="submit" type="submit" value="Save" class="k-button k-primary" />',
                '<a href="' . \Natty::url('backend/people/role') . '" class="k-button">Back</a>'
        ));


        // Handle submission
        if ( isset ($_POST['submit']) ):
            
            foreach ( $role_coll as $rid => $role ):
                
                $dbo->beginTransaction();
            
                // Erase existing permission records
                $dbo->delete($tablename, array (
                    'key' => array ('rid' => $rid),
                ));
                
                // Assign permissions
                if ( !isset ($_POST['items'][$rid]) )
                    $_POST['items'][$rid] = array ();
                
                foreach ( $_POST['items'][$rid] as $aid ):
                    
                    // Must be a valid action
                    if ( !isset ($action_coll[$aid]) )
                        continue;
                    
                    $dbo->insert($tablename, array (
                        'rid' => $rid,
                        'aid' => $aid,
                    ));
                    
                endforeach;
                
                $dbo->commit();
                
            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            
            $bounce_url = 'backend/people/permissions';
            if ( 1 === sizeof($role_coll) )
                $bounce_url = 'backend/people/role';
            
            \Natty::getResponse()->redirect($bounce_url);

        endif;

        // Prepare output
        $output = array ();
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            '_form' => $list_form,
        );
        
        return $output;
        
    }
    
    public static function pageActions() {
        
        $request = \Natty::getRequest();
        $entity = $request->getEntity('with', 'people--role');

        // Execute action
        switch ( $request->getString('do') ):
            case 'delete':
                
                if ( $entity->isLocked )
                    \Natty::error(400);
                $entity->delete();
                
                \Natty\Console::success();
                
                break;
            default:
                \Natty\Console::error(NATTY_ACTION_UNRECOGNIZED);
                break;
        endswitch;

        \Natty::getResponse()->bounce();
        
    }
    
}