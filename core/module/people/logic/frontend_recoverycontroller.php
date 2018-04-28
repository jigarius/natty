<?php

namespace Module\People\Logic;

class Frontend_RecoveryController {
    
    public static function pageForgotPassword() {
        
        // Logged in users cannot view the page
        if ( \Natty::getUser()->uid )
            \Natty::error(404);
        
        // Load dependencies
        $user_handler = \Natty::getHandler('people--user');
        $token_handler = \Natty::getHandler('people--token');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'people-forgot-password',
        ));
        
        $form->items['default']['_label'] = 'Identify yourself';
        $form->items['default']['_data']['email'] = array (
            '_label' => 'Email address',
            '_description' => 'Enter the email address which is associated with your account.',
            '_widget' => 'input',
            'type' => 'email',
            'required' => 1,
        );
        
        $form->actions['recover'] = array (
            '_label' => 'Recover',
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted('recover') ):
            
            $form_data = $form->getValues();
            
            // Attempt to identify the email address
            $user = $user_handler->read(array (
                'key' => array ('email' => $form_data['email']),
                'unique' => 1,
            ));
            if ( !$user ):
                $form->items['default']['_data']['email']['_errors'][] = 'The email address you entered was not found in our records. Make sure you are entering the correct email address and try again.';
                $form->isValid(FALSE);
            endif;
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            // Check for active tokens
            $token = $token_handler->read(array (
                'key' => array (
                    'uid' => $user->uid,
                    'purpose' => 'one time access',
                ),
                'unique' => 1,
            ));
            if ( $token && strtotime($token->dtExpired) < time() ):
                $token->delete();
                $token = FALSE;
            endif;
            
            // Create token if not exists
            if ( !$token ):
                $token = $token_handler->createAndSave(array (
                    'uid' => $user->uid,
                    'purpose' => 'one time access',
                    'dtExpired' => date('Y-m-d H:i:s', strtotime('+6 hours')),
                ));
            endif;
            
            // Send email notification
            $email = \Natty::getEntity('system--email', 'people--account recovery');
            $email->send(array (
                'recipientUser' => $user,
                'data' => array (
                    'user' => $user,
                    'token' => $token,
                    'link' => '<a href="' . \Natty::url('user/ota/' . $token->tid) . '">',
                    '/link' => '</a>',
                ),
            ));
            
            $message = 'An account recovery email has been sent to ' . $user->email . '. Please follow the instructions therein to recover your account.';
            \Natty\Console::message($message);
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function pageOneTimeAccess($token) {
        
        // User must not be signed in
        if ( \Natty::getUser()->uid > 0 )
            \Natty::error(403);
        
        // Validate token
        if ( $token->purpose != 'one time access' )
            \Natty::error(400);
        if ( strtotime($token->dtExpired) < time() )
            \Natty::error(400);
        
        // Load dependencies
        $user_handler = \Natty::getHandler('people--user');
        $user = $user_handler->readById($token->uid);
        
        // User should not be blocked
        if ( !$user->status ):
            \Natty\Console::error('Your account has been blocked. Please contact the administrator for further information.');
            $location = \Natty::url('sign-in');
            \Natty::redirect($location);
        endif;
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'people-ota-form',
        ));
        $form->actions['ota'] = array (
            '_label' => 'One time login',
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            $form->onValidate();
        endif;
        
        // Process form
        if ( $form->isSubmitted('ota') && $form->isValid() ):
            
            // Delete the token
            $token->delete();
            
            // Sign the user in and redirect
            $user_handler::setAuthUserId($user->uid);
            $form->redirect = \Natty::url('dashboard/user/edit-profile');
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output = array (
            '_render' => 'template',
            '_template' => 'module/people/tmpl/ota.tmpl',
            '_data' => array (
                'user' => $user,
                'form' => $form->getRarray(),
            ),
        );
        
        return $output;
        
    }
    
}