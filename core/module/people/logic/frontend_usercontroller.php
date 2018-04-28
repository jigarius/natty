<?php

namespace Module\People\Logic;

use Module\People\Classes\UserHandler;

class Frontend_UserController {

    public static function pageSignIn() {

        $auth_user = \Natty::getUser();

        /**
         * If a user is already signed in
         */
        if ($auth_user->uid):

            $location = \Natty::getRequest()->getString('bounce');
            if (!$location && $auth_user->can('system--view dashboard'))
                $location = \Natty::url('dashboard');
            if (!$location)
                $location = NATTY_BASE;

            \Natty::getResponse()->redirect($location);

        endif;

        /**
         * Create authentication form
         */
        $form = new \Natty\Form\FormObject(array(
            'id' => 'people-sign-in',
        ));

        $form->items['default']['_label'] = 'Your credentials';
        $form->items['default']['_data']['username'] = array(
            '_widget' => 'input',
            '_label' => 'Username',
            'autofocus' => 'on',
            'required' => TRUE,
        );
        $form->items['default']['_data']['password'] = array(
            '_widget' => 'input',
            '_label' => 'Password',
            '_description' => '<a href="' . \Natty::url('user/forgot-password') . '">Forgot your password?</a>',
            'type' => 'password',
            'required' => TRUE,
        );

        // Instruction for account creation
        if (\Natty::readSetting('people--userRegiEnabled')):
            $form->items['default']['_data']['username']['_description'] = '<a href="' . \Natty::url('sign-up') . '">Don\'t have an account?</a>';
        endif;

        $form->actions['submit'] = array(
            '_widget' => 'button',
            '_label' => 'Sign in'
        );

        $form->onPrepare();

        // Validate
        if ($form->isSubmitted()):

            $form->onValidate();

        endif;

        // Handle form submission
        if ($form->isValid()):

            $options = $form->getValues();

            // Determine identifier
            if (FALSE === strpos($options['username'], '@')) {
                $options['alias'] = $options['username'];
            } else {
                $options['email'] = $options['username'];
            }

            // Attempt authentication
            switch (UserHandler::authenticate($options)):

                case UserHandler::AUTH_ERR_OK:
                    \Natty\Console::success('You have signed in successfully!', array(
                        'heading' => 'Signed in'
                    ));
                    \Natty\Core\Response::refresh();
                    break;

                case UserHandler::AUTH_ERR_PASSWORD:
                    $message = natty_replace(array('username' => $options['username']), 'You entered the wrong password for [@username]. Please type your password carefully and try again.');
                    break;

                case UserHandler::AUTH_ERR_NOTFOUND:
                    $message = natty_replace(array('username' => $options['username']), 'The username [@username] was not recognized. Please check to see if you spelled it correctly.');
                    break;

                case UserHandler::AUTH_ERR_DISABLED:
                    $message = natty_replace(array('username' => $options['username']), 'The account associated with [@username] is disabled. Please contact the administrator for further details.');
                    break;

                case UserHandler::AUTH_ERR_PENDING:
                    $message = natty_replace(array('username' => $options['username']), 'The account associated with [@username] is pending approval / email validation.');
                    break;

                case UserHandler::AUTH_ERR_MULTIFAIL:
                    $message = natty_replace(array('username' => $options['username']), 'Login for [@username] has been disabled temporarily due to multiple failed login attempts. Please try logging in after 6 hours or initiate password recovery.');
                    break;

                default:
                    $message = 'An unknown error ocurred during authentication!';
                    break;

            endswitch;

            \Natty\Console::error($message, array(
                'heading' => 'Sign in failed',
            ));

        endif;

        return $form->getRarray();
    }

    public static function pageSignOut() {

        \Module\People\Classes\UserHandler::unauthenticate();

        \Natty\Console::success('You have signed out successfully.', array(
            'heading' => 'Signed out',
        ));

        \Natty\Core\Response::redirect('sign-in');
    }

    public static function pageSignUp() {

        // Is registration enabled?
        if (!\Natty::readSetting('people--userRegiEnabled'))
            \Natty::error(404);

        // Load dependencies
        $dbo = \Natty::getDbo();
        $user_handler = \Natty::getHandler('people--user');

        // Build the registration form
        $form = new \Natty\Form\FormObject(array(
            'id' => 'people-sign-up',
        ));

        // Basic Info
        $form->items['default']['_label'] = 'Account Information';
        $form->items['default']['_data']['name'] = array(
            '_widget' => 'input',
            '_label' => 'Full name',
            'maxlength' => 255,
            'required' => TRUE,
        );
        $form->items['default']['_data']['email'] = array(
            '_widget' => 'input',
            '_label' => 'Email address',
            'type' => 'email',
            'maxlength' => 128,
            'required' => TRUE,
        );
        $form->items['default']['_data']['password'] = array(
            '_label' => 'Choose a password',
            '_widget' => 'input',
            'type' => 'password',
            'widget' => 'password',
            'maxlength' => 16,
            'required' => TRUE,
        );
        $form->items['default']['_data']['password_conf'] = array(
            '_label' => 'Confirm password',
            '_widget' => 'input',
            '_ignore' => TRUE,
            'type' => 'password',
            'maxlength' => 16,
            'required' => TRUE,
        );

        // Buttons
        $form->actions['register'] = array(
            '_label' => 'Sign up'
        );
        $form->actions['login'] = array(
            '_label' => 'Sign in',
            'type' => 'anchor',
            'href' => \Natty::url('sign-in'),
        );

        $form->onPrepare();

        // Custom validation
        if ($form->isSubmitted()):

            // See if the password was confirmed
            $form_data = $form->getValues();

            // Password minimum length
            if (strlen($form_data['password']) < 6):
                $form->isValid(FALSE);
                $form->items['default']['_data']['password']['_errors'][] = 'Value must be at least 6 characters long.';
            endif;

            if ($form_data['password'] != $_POST['password_conf']):
                $form->isValid(FALSE);
                $form->items['default']['_data']['password_conf']['_errors'][] = 'Value must match with original password.';
            endif;

            // Detect conflicts
            $conflict = $user_handler->read(array(
                'key' => array('email' => $form_data['email']),
            ));

            if ($conflict):
                $form->items['default']['_data']['username']['_errors'][] = 'This email address is associated with another account.';
                $form->isValid(FALSE);
            endif;

            $form->onValidate();

        endif;

        // Handle form submission
        if ($form->isValid()):

            // Save account info
            $user = $user_handler->create($form_data);
            $user->status = 1;
            $user->save();

            // Assign default user group
            $dbo->insert('%__people_user_role_map', array(
                'uid' => $user->uid,
                'rid' => \Module\People\Classes\RoleHandler::ID_MEMBER,
            ));

            // Validation token
            $token_handler = \Natty::getHandler('people--token');
            $token = $token_handler->createAndSave(array(
                'uid' => $user->uid,
                'purpose' => 'email validation',
            ));

            // Send an email to the user
            $email = \Natty::getEntity('system--email', 'people--email validation');
            $email->send(array(
                'recipientUser' => $user,
                'data' => array(
                    'user' => $user,
                    'token' => $token,
                    'link' => '<a href="' . \Natty::url('user/vem/' . $token->tid) . '">',
                    '/link' => '</a>'
                ),
            ));

            // Redirect to sign in page
            $message = 'Thank you for registering with us. However, you need to activate your account to be able to sign in. Please follow the instructions mailed to ' . $user->email . ' to activate your account.';
            \Natty\Console::success($message, array(
                'heading' => 'Validate your email',
            ));

            $form->redirect = \Natty::url('sign-in');
            $form->onProcess();

        endif;

        // Prepare output
        return $form->getRarray();
    }

    public static function pageValidateEmail($token) {

        // User must not be signed in
        if (\Natty::getUser()->uid > 0)
            \Natty::error(400);

        // Validate token
        if ($token->purpose != 'email validation')
            \Natty::error(400);
        if (!empty($token->dtExpired) && strtotime($token->dtExpired) < time())
            \Natty::error(400);

        // Load dependencies
        $user_handler = \Natty::getHandler('people--user');

        // Activate the account
        $user_handler::setAuthUserId($token->uid);
        $token->delete();

        // Redirect
        $location = \Natty::url('sign-in');
        \Natty::getResponse()->redirect($location);
    }

}
