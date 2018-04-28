<?php

namespace Module\Contact\Logic;

use \Natty\Helper\EmailHelper;
use \Natty\Form\FormObject;

class Frontend_DefaultController {
    
    public static function pageForm() {
        
        $auth_user = \Natty::getUser();

        // Read categories
        $category_opts = \Natty::getHandler('contact--category')->readOptions();

        // Prepare form
        $form = new FormObject(array (
            'id' => 'contact-form',
        ));
        $fset_default =& $form->items['default']['_data'];

        $fset_default['senderName'] = array (
            '_label' => 'Your Name',
            '_widget' => 'input',
            'maxlength' => 128,
        );
        $fset_default['senderEmail'] = array (
            '_label' => 'Your Email',
            '_widget' => 'input',
            'type' => 'email',
            'maxlength' => 128,
        );
        $fset_default['category'] = array (
            '_label' => 'Category',
            '_description' => 'Pick the most relevant category for your message.',
            '_widget' => 'dropdown',
            '_options' => $category_opts,
            '_default' => '',
            '_display' => $category_opts,
            'placeholder' => '',
            'required' => $category_opts,
        );
        $fset_default['subject'] = array (
            '_label' => 'Subject',
            '_widget' => 'input',
            'required' => 1,
            'maxlength' => 128,
        );
        $fset_default['message'] = array (
            '_label' => 'Your Message',
            '_widget' => 'textarea',
            'required' => 1,
        );

        // If a user is logged in, pre-fill data
        if ( $auth_user->uid > 0 ):
            $fset_default['senderName']['readonly'] = 1;
            $fset_default['senderName']['_default'] = $auth_user->name;
            $fset_default['senderEmail']['readonly'] = 1;
            $fset_default['senderEmail']['_default'] = $auth_user->email;
        endif;

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            // Prepare values for mail
            $form_values = $form->getValues();
            $form_values['message'] = htmlentities($form_values['message']);

            // Load contact category (if any)
            $ccat = FALSE;
            if ( $form_values['category'] )
                $ccat = \Natty::getEntity('contact--category', $form_values['category']);

            // Send an email with the values
            $email = array ();

            $email['mimeType'] = 'text/html';

            $email['subject'] = $form_values['subject'] . ' - ' . \Natty::readSetting('system--siteName');

            $email['content'] = '';
            $email['content'] .= '<p>' . str_replace(array ("\r\n", "\n"), '</p><p>', $form_values['message']) . '</p>';
            $email['content'] .= '<p>'
                        . '<strong>Sender Name: </strong>' . $form_values['senderName'] . '<br />'
                        . '<strong>Sender Email: </strong>' . $form_values['senderEmail'] . '<br />'
                    . '</p>';

            $send_options = array (
                'senderName' => $form_values['senderName'],
                'recipients' => array (),
            );

            // Send to category recipients
            if ( $ccat ):
                $ccat_recipients = preg_split('/[\s]*,[\s]*/', $ccat->recipients);
                $send_options['recipients'] = array_merge($send_options['recipients'], $ccat_recipients);
            endif;

            // Validate recipients
            if ( 0 === sizeof($send_options['recipients']) )
                \Natty::error(500, 'Configuration error. No recipients found.');

            // Attempt to send mail
            if ( !EmailHelper::send($email, $send_options) ) {
                \Natty\Console::error('Message could not be sent.');
            }
            else {

                \Natty\Console::success('Message sent successfully.');

                $form->redirect = NATTY_BASE;
                $form->onProcess();

            }

        endif;

        // Prepare output
        $output[] = $form->getRarray();
        
        return $output;
        
    }
    
}