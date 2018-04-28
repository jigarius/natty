<?php

namespace Module\Example\Logic;

use Natty\Helper\EmailHelper;

class EmailController {
    
    public static function pageBasics() {
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'example-core-email-form',
        ));
        $form->items['default']['_label'] = 'Recipient info';
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            'maxlength' => 255,
        );
        $form->items['default']['_data']['email'] = array (
            '_label' => 'Email address',
            '_widget' => 'input',
            'type' => 'email',
            'maxlength' => 128,
        );

        $form->actions['send'] = array (
            '_label' => 'Send',
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            // Get form values i.e. recipeint info
            $form_data = $form->getValues();
            
            // The first argument only defines the template for the email
            // The second argument contains data related to the recipient
            $email['status'] = EmailHelper::send(array (
                'subject' => 'Simple email example',
                'content' => 'Hello ' . $form_data['name'] . '. This is a simple example email.',
            ), array (
                'recipientEmail' => $form_data['email'],
                'recipientName' => $form_data['name'],
            ));

            // Set a notice message
            if ( $email['status'] ) {
                \Natty\Console::success();
            }
            else {
                \Natty\Console::error();
            }

            $form->onProcess();

        endif;

        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function pageAdvanced() {
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'example-core-email-form',
        ));
        $form->items['default']['_label'] = 'Recipient info';
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            'maxlength' => 255,
        );
        $form->items['default']['_data']['email'] = array (
            '_label' => 'Email address',
            '_widget' => 'input',
            'type' => 'email',
            'maxlength' => 128,
        );

        $form->actions['send'] = array (
            '_label' => 'Send',
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            // Get form values i.e. recipeint info
            $form_data = $form->getValues();
            
            // Prepare a recipient object
            $recipient = (object) $form_data;
        
            // Load the email object
            $email = \Natty::getEntity('system--email', 'example--advanced email');
            
            try {
                $email->send(array (
                    // Recipient data
                    'recipientEmail' => $form_data['email'],
                    'recipientName' => $form_data['name'],
                    // Data to be replaced in email subject and content
                    'data' => array (
                        'recipient' => $recipient,
                    ),
                    'preview' => TRUE,
                ));
                \Natty\Console::success();
            }
            catch(\Exception $ex) {
                \Natty\Console::error();
                $form->rebuild = TRUE;
            }

            $form->onProcess();

        endif;

        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}