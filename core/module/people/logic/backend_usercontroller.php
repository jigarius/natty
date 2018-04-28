<?php

namespace Module\People\Logic;

class Backend_UserController {
    
    public static function pageManage() {
        
        $auth_user = \Natty::getUser(TRUE);
        $user_handler = \Natty::getHandler('people--user');

        // Prepare a list of available roles
        $role_handler = \Natty::getHandler('people--role');
        $roles = $role_handler->readOptions(array (
            'condition' => array ('{status}', '=', '1'),
            'ordering' => array ('name' => 'desc')
        ));

        // Prepare paging
        $query = \Natty::getDbo()
                ->getQuery('select', '%__people_user user')
                ->addColumns(array ('uid', 'alias', 'name', 'email', 'status'), 'user')
                ->addComplexCondition(array ('uid', '>', 1));

        $list_head = array (
            array ('_data' => 'Name'),
            array ('class' => array ('n-ta-ce'), '_data' => 'Active'),
            array ('class' => array ('context-menu'), '_data' => '')
        );

        $list = new \Natty\Helper\PagingHelper($query);
        $list_data = $list->execute();

        // Prepare rows of the table
        $list_body = array ();
        foreach ( $list_data['items'] as $key => $item ):

            $item = $user_handler->create($item);

            $row = array ();

            $row['name'] = array (
                '<div class="prop-title">' . $item->name . ($item->alias ? ' | ' . $item->alias : '') . '</div>',
                '<div class="prop-description">Last accessed: ' . natty_format_datetime($item->dtAccessed) . '</div>'
            );

            $row['status'] = array (
                'class' => array ('n-ta-ce'),
                '_data' => $item->status ? 'Yes' : 'No'
            );

            $row['context-menu'] = $item->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Add rendering data
        $output = [];
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/people/user/create') . '" class="k-button">Create</a>'
            )
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $user) {
        
        // Load dependencies
        $dbo = \Natty::getDbo();
        $auth_user = \Natty::getUser();
        $user_handler = \Natty::getHandler('people--user');
        $role_handler = \Natty::getHandler('people--role');
        
        // Determine admin mode
        $admin_mode = (bool) $auth_user->can('people--manage user entities');

        // Determine the user to edit
        if ( 'self' == $user )
            $user = $auth_user;

        // Creation Request
        if ( 'create' == $mode ) {
            $user = $user_handler->create();
            $existing_roles = array ();
        }
        // Modification Request
        else {
            $existing_roles = $dbo
                    ->getQuery('select', '%__people_user_role_map urm')
                    ->addColumn('rid')
                    ->addComplexCondition('AND', array ('urm.uid', '=', ':uid'))
                    ->execute(array ('uid' => $user->uid))
                    ->fetchAll(\PDO::FETCH_COLUMN);
        }

        /**
         * Build an array of roles for the role selector
         */
        $role_options = $role_handler->readOptions(array (
            'condition' => "{status} >= 0",
        ));

        // Build a user form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'people-user-edit-form',
        ), array (
            'etid' => 'people--user',
            'entity' => &$user,
        ));

        // Personal Info
        $form->items['default']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            '_default' => $user->name,
            'required' => TRUE,
            'maxlength' => 128,
            'autocomplete' => 'off',
        );
        $form->items['default']['_data']['alias'] = array (
            '_widget' => 'input',
            '_label' => 'Alias',
            '_default' => $user->alias,
            'maxlength' => 32,
            'autocomplete' => 'off',
        );
        if ( !$admin_mode || !$auth_user->can('people--edit own alias') )
            $form->items['default']['_data']['alias']['readonly'] = TRUE;
        
        $form->items['default']['_data']['email'] = array (
            '_widget' => 'input',
            '_label' => 'E-mail Address',
            'type' => 'email',
            'maxlength' => 128,
            '_default' => $user->email,
            'autocomplete' => 'off',
        );
        $form->items['default']['_data']['password'] = array (
            '_widget' => 'input',
            '_label' => 'Password',
            '_description' => 'Leave blank to keep password unchanged.',
            'type' => 'password',
            'minlength' => 6,
            'maxlength' => 16,
            'autocomplete' => 'off',
        );
        $form->items['default']['_data']['password_conf'] = array (
            '_widget' => 'input',
            '_label' => 'Re-type password',
            '_description' => 'Re-type the password in order to confirm it.',
            '_ignore' => 1,
            'minlength' => 6,
            'maxlength' => 16,
            'type' => 'password',
            'autocomplete' => 'off',
        );
        $form->items['default']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_options' => array (
                1 => 'Active',
                0 => 'Blocked'
            ),
            'class' => array ('options-inline'),
            'required' => TRUE,
            '_default' => $user->status
        );

        // Role options
        if ( $auth_user->can('people--manage user entities') ):
            $form->items['role-conf'] = array (
                '_widget' => 'container',
                '_label' => 'Role Configuration'
            );
            $form->items['role-conf']['_data']['roles'] = array (
                '_widget' => 'options',
                '_label' => 'Roles',
                '_options' => $role_options,
                '_default' => $existing_roles,
                'class' => array ('options-block'),
                'required' => 1,
                'multiple' => 'multiple',
                'disabled' => (1 == $user->uid),
            );
        endif;

        // Actions
        $form->actions['save'] = array (
            '_label' => 'Save',
            'type' => 'submit',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/people/user')
        );

        $form->scripts[] = array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'people') . '/reso/backend_userformhelper.js',
        );
        
        // Prepare form
        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form_data = $form->getValues();
            
            // If password is enetered
            if ( $form_data['password'] ):
                
                // Validate password confirmation
                if ( $form_data['password'] != $_POST['password_conf'] ):
                    $form->items['default']['_data']['password_conf']['_errors'][] = 'The two passwords do not match.';
                    $form->isValid(FALSE);
                endif;
                
            endif;
            
            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):
            
            $user->setState($form_data);
            $user->save();

            // Update role assignment
            if ( $admin_mode ):

                $dbo->beginTransaction();

                // Delete present roles
                if ( $user->uid ):
                    $dbo->delete('%__people_user_role_map', array (
                        'key' => array ('uid' => $user->uid),
                    ));
                endif;

                // Set new roles
                if ( $form_data['roles'] ):
                    foreach ( $form_data['roles'] as $rid ):
                        $record = array ('uid' => $user->uid, 'rid' => $rid);
                        $dbo->insert('%__people_user_role_map', $record);
                    endforeach;
                endif;

                $dbo->commit();
                
            endif;
            
            \Natty\Console::success();

            if ('create' == $mode)
                $form->redirect = 'backend/people/user';
            else
                $form->redirect = 'backend/people/user/' . $user->uid;

            $form->onProcess();

        endif;

        // Prepare response
        $output = $form->getRarray();
        return $output;
        
    }
    
    public static function pageActions() {
        
        $request = \Natty::getRequest();
        $entity = $request->getEntity('with', 'people--user');

        natty_debug($entity);
        
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