<?php

namespace Module\System\Logic;

use Natty\Helper\FileHelper;

class Backend_SettingsController {
    
    public static function pageSiteInfoForm() {
        
        // Logo upload paths
        $stor_logo_filename = 'instance://logo.png';

        // Build a form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-settings-site-info-form',
            'enctype' => 'multipart/form-data',
        ));

        // Add fields to the form
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info'
        );
        $form->items['basic']['_data']['system--siteName'] = array (
            '_widget' => 'input',
            '_label' => 'Site Name',
            '_default' => \Natty::readSetting('system--siteName'),
            '_description' => 'The name of your website.'
        );
        $form->items['basic']['_data']['system--siteCaption'] = array (
            '_widget' => 'input',
            '_label' => 'Site Caption',
            '_default' => \Natty::readSetting('system--siteCaption'),
            '_description' => 'A tag-line / caption for your website.',
        );
        $form->items['basic']['_data']['logo'] = array (
            '_widget' => 'upload',
            '_label' => 'Logo - Standard',
            '_extensions' => array ('png'),
            '_default' => FileHelper::glob($stor_logo_filename, array (
                'unique' => 1,
            )),
            '_ignore' => 1,
        );

        $form->items['route'] = array (
            '_widget' => 'container',
            '_label' => 'URL',
        );
        $form->items['route']['_data']['system--routeDefault'] = array (
            '_widget' => 'input',
            '_label' => 'Home Page',
            '_default' => \Natty::readSetting('system--routeDefault'),
            '_description' => 'Choose a home page. Defaults to the <em>sign-in</em> page.',
        );
        $form->items['route']['_data']['system--routeClean'] = array (
            '_widget' => 'options',
            '_label' => 'URL Style',
            '_default' => \Natty::readSetting('system--routeClean'),
            '_options' => array (
                1 => 'Clean: site.com/foo/bar',
                0 => 'Dirty: site.com/index.php?_command=foo/bar',
            ),
            '_description' => 'For clean URLs, apache <strong>mod-rewrite</strong> must be enabled.',
        );
        $form->items['route']['_data']['system--routeRewrite'] = array (
            '_widget' => 'options',
            '_label' => 'URL Re-writing',
            '_default' => \Natty::readSetting('system--routeRewrite'),
            '_options' => array (
                1 => 'On',
                0 => 'Off',
            ),
            '_description' => 'Allow provision for SEO friendly URLs.',
            'class' => array ('options-inline'),
        );

        $form->items['email'] = array (
            '_widget' => 'container',
            '_label' => 'Email',
        );
        $form->items['email']['_data']['system--siteEmail'] = array (
            '_label' => 'Outgoing Email',
            '_description' => 'Outgoing mails would be marked as sent by this address.<br />Example: noreply@site.com',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--siteEmail'),
            'placeholder' => 'noreply@' . $_SERVER['HTTP_HOST'],
        );
        $form->items['email']['_data']['system--emailEnabled'] = array (
            '_label' => 'Email notifications',
            '_description' => 'Whether messgaes would be sent for events.<br />Example: email validation, password recovery, password updation, etc.',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => \Natty::readSetting('system--emailEnabled'),
            'required' => 1,
        );
        $form->items['email']['_data']['system--emailMethod'] = array (
            '_label' => 'Mailing Method',
            '_widget' => 'options',
            '_options' => array (
                'sendmail' => 'Sendmail'
            ),
            '_default' => \Natty::readSetting('system--emailMethod'),
            'required' => 1,
        );

        $form->actions['save'] = array (
            'type' => 'submit',
            '_label' => 'Save'
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            unset ($form_values['logo']);
            foreach ( $form_values as $param_name => $param_value ):
                \Natty::writeSetting($param_name, $param_value);
            endforeach;

            // Process uploads
            $fitem_logo = $form->items['basic']['_data']['logo'];
            if ( $fitem_logo['_default'] && isset ($fitem_logo['_value']['deleted']) ):
                FileHelper::unlink($stor_logo_filename);
                \Natty::writeSetting('system--siteLogo', NULL);
            endif;
            if ( isset ($fitem_logo['_value']['temporary']) ):
                FileHelper::moveUpload($fitem_logo['_value'], $stor_logo_filename);
                \Natty::writeSetting('system--siteLogo', $stor_logo_filename);
            endif;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        return $form->getRarray();
        
    }
    
    public static function pageOfflineModeForm() {
        
        // Build a form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-settings-offline-mode',
        ));

        // Add fields to the form
        $form->items['default']['_data']['system--offlineModeEnabled'] = array (
            '_widget' => 'options',
            '_label' => 'Site Status',
            '_options' => array (
                0 => 'Online',
                1 => 'Offline',
            ),
            '_default' => \Natty::readSetting('system--offlineModeEnabled'),
            'class' => array ('options-inline'),
        );
        $form->items['default']['_data']['system--offlineModeMessage'] = array (
            '_widget' => 'textarea',
            '_label' => 'Message',
            '_description' => 'If the site is offline, this message will be displayed to visitors.',
            '_default' => \Natty::readSetting('system--offlineModeMessage'),
        );

        $form->actions['save'] = array (
            'type' => 'submit',
            '_label' => 'Save'
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            foreach ( $form_values as $param_name => $param_value ):
                \Natty::writeSetting($param_name, $param_value);
            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        return $form->getRarray();
        
    }
    
    public static function pageCronForm() {
        
        // Read last cron run
        $last_exec = \Natty::readSetting('system--cronExecTime');
        $last_exec = $last_exec
                ? date('d M, Y @ h:i a') : 'Never';

        // Build a form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-settings-cron'
        ));
        $form->items['basic'] = array (
            '_widget' => 'container',
        );
        $form->items['basic']['_data']['system--cronExecTime'] = array (
            '_label' => 'Last Executed',
            '_widget' => 'markup',
            '_markup' => $last_exec,
            '_ignore' => TRUE,
        );
        $form->items['basic']['_data']['system--cronInterval'] = array (
            '_label' => 'Cron Interval',
            '_widget' => 'dropdown',
            '_options' => array (
        //        0 => 'Never',
                1 => '1 hour',
                3 => '3 hours',
                6 => '6 hours',
                12 => '12 hours',
            ),
            '_default' => \Natty::readSetting('system--cronInterval'),
            '_description' => 'The cronic maintenance tasks would be run at this interval.'
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['execute'] = array (
            '_label' => 'Run Cron',
            'data-ui-init' => array ('confirmation'),
            'data-confirmation' => 'Are you sure you want to run cron now?',
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Run cron?
        if ( $form->isSubmitted('execute') ):

            \Natty::getPackage('module', 'system')->executeCron();
            \Natty\Console::success('Cron was executed.');

            $form->redirect = \Natty::getRequest()->getUri();

            $form->onProcess();

        endif;

        // Save data?
        if ( $form->isSubmitted('save') && $form->isValid() ):

            $form_values = $form->getValues();
            foreach ( $form_values as $name => $value ):
                \Natty::writeSetting($name, $value);
            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

            $form->onProcess();

        endif;

        // Prepare document
        return $form->getRarray();
        
    }
    
    public static function pageLocaleSettings() {
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'locale-settings-form',
        ));
        $form->items['default']['_label'] = 'General';
        
        $opts_language = \Natty::getHandler('system--language')
                ->readOptions(array (
                    'conditions' => array (
                        array ('AND', '{status} = 1')
                    ),
                ));
        $form->items['default']['_data']['system--language'] = array (
            '_label' => 'Default language',
            '_widget' => 'dropdown',
            '_options' => $opts_language,
            '_default' => \Natty::readSetting('system--language'),
            'placeholder' => '',
            'required' => 1,
        );
        $form->items['default']['_data']['system--i18n'] = array (
            '_label' => 'Multiple languages',
            '_description' => 'Warning: This feature is not fully supported at the time.',
            '_widget' => 'input',
            'type' => 'checkbox',
            '_default' => \Natty::readSetting('system--i18n'),
            'class' => array ('options-inline'),
        );
        $form->items['default']['_data']['system--country'] = array (
            '_label' => 'Locations',
            '_description' => 'Manage countries, states and region data and availability.',
            '_widget' => 'markup',
            '_markup' => '<a href="' . \Natty::url('backend/location/countries') . '" class="k-button" target="_blank">Manage</a>',
            '_ignore' => 1,
        );
        
        $opts_currency = \Natty::getHandler('system--currency')
                ->readOptions(array (
                    'conditions' => array (
                        array ('AND', '{status} = 1')
                    ),
                ));
        $form->items['default']['_data']['system--currency'] = array (
            '_label' => 'Default currency',
            '_widget' => 'dropdown',
            '_options' => $opts_currency,
            '_default' => \Natty::readSetting('system--currency'),
            'placeholder' => '',
            'required' => 1,
        );
        
        $form->items['datetime'] = array (
            '_widget' => 'container',
            '_label' => 'Date & Time',
            '_data' => array (),
        );
        $form->items['datetime']['_data']['system--timezone'] = array (
            '_label' => 'Default timezone',
            '_widget' => 'dropdown',
            '_options' => array (
                'Asia/Calcutta' => 'Asia/Calcutta'
            ),
            '_default' => \Natty::readSetting('system--timezone'),
            'placeholder' => '',
            'required' => 1,
        );
        $form->items['datetime']['_data']['system--datetimeDateOnly'] = array (
            '_label' => 'Date format',
            '_description' => 'Enter a PHP date format string. For details, read documentation on PHP\'s website.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--datetimeDateOnly', 'd M, Y'),
            'maxlength' => 16,
            'required' => 1,
        );
        $form->items['datetime']['_data']['system--datetimeTimeOnly'] = array (
            '_label' => 'Time format',
            '_description' => 'Enter a PHP date format string. For details, read documentation on PHP\'s website.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--datetimeTimeOnly', 'h:i A'),
            'maxlength' => 16,
            'required' => 1,
        );
        $form->items['datetime']['_data']['system--datetimeDateTime'] = array (
            '_label' => 'Date & time format',
            '_description' => 'Enter a PHP date format string. For details, read documentation on PHP\'s website.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--datetimeDateTime', 'd M, Y @ h:i A'),
            'maxlength' => 16,
            'required' => 1,
        );
        
        $form->items['uom'] = array (
            '_widget' => 'container',
            '_label' => 'Measurement',
            '_data' => array (),
        );
        $form->items['uom']['_data']['system--weightUnit'] = array (
            '_label' => 'Weight unit',
            '_description' => 'Example: lbs or kg.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--weightUnit'),
        );
        $form->items['uom']['_data']['system--shortLengthUnit'] = array (
            '_label' => 'Measurement unit',
            '_description' => 'Example: in or cm.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--shortLengthUnit'),
        );
        $form->items['uom']['_data']['system--longLengthUnit'] = array (
            '_label' => 'Distance unit',
            '_description' => 'Example: mi or km.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--longLengthUnit'),
        );
        $form->items['uom']['_data']['system--volumeUnit'] = array (
            '_label' => 'Volume unit',
            '_description' => 'Example: l or gal.',
            '_widget' => 'input',
            '_default' => \Natty::readSetting('system--volumeUnit'),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save'
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $form_data = $form->getValues();
            foreach ( $form_data as $name => $value ):
                \Natty::writeSetting($name, $value);
            endforeach;
            
            \Natty\Console::success();
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}