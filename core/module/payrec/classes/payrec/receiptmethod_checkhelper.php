<?php

namespace Module\Payrec\Classes\Payrec;

class ReceiptMethod_CheckHelper
extends \Module\Payrec\Classes\ReceiptMethodHelperAbstract {
    
    public static function handleSettingsForm(&$data) {
        
        $form =& $data['form'];
        $method =& $data['entity'];
        
        $method->settings = array_merge(self::getDefaultSettings(), $method->settings);
        
        switch ($form->getStatus()):
            case 'prepare':
                $form->items['settings']['_data']['settings.payeeName'] = array (
                    '_label' => 'Payee name',
                    '_widget' => 'input',
                    '_default' => $method->settings['payeeName'],
                    'maxlength' => 255,
                );
                $form->items['settings']['_data']['settings.payeeAddress'] = array (
                    '_label' => 'Payee address',
                    '_description' => 'Users would be asked to send their checks to this address.',
                    '_widget' => 'textarea',
                    '_default' => $method->settings['payeeAddress'],
                    'maxlength' => 1024,
                );
                $form->items['settings']['_data']['settings.notes'] = array (
                    '_label' => 'Notes',
                    '_widget' => 'textarea',
                    '_default' => $method->settings['notes'],
                    'maxlength' => 1024,
                );
                break;
        endswitch;
        
    }
    
    public static function doTransaction(&$tran, $method) {
        
        // Save the transaction
        if (!$tran->tid)
            $tran->save();
        
        // Send an email to the payee
        $email_options = array (
            'recipientName' => $tran->creatorName,
            'recipientEmail' => $tran->creatorEmail,
            'data' => array (
                'tran' => $tran,
                'method' => $method,
            ),
        );
        $email = \Natty::getEntity('system--email', 'payrec--check receipt pending');
        $email->send($email_options);
        
        // Redirect 
        $location = \Natty::url('backend/payrec/trans/' . $tran->tid);
        \Natty::getResponse()->redirect($location);
        
    }
    
    public static function attachView($tran, array &$build) {
        
        $pmethod = \Natty::getEntity('payrec--method', $tran->mid);
        $allow_verify = $tran->call('allowAction', 'verify') && (0 === (int) $tran->status);
        
        // Load existing check data
        $check_data = self::readCheckData($tran->tid);
        
        // Bounce back
        $bounce_url = \Natty::url($tran->contextUrl);
        if (isset ($_REQUEST['bounce']))
            $bounce_url = $_REQUEST['bounce'];
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'payrec-tran-view',
        ), array (
            'etid' => 'payrec--tran',
            'entity' => &$tran,
        ));
        
        $form->items['instruction'] = array (
            '_display' => (0 === (int) $tran->status),
            '_widget' => 'container',
            '_label' => 'How to pay?',
            '_data' => array (
                'instruction' => array (
                    '_render' => 'template',
                    '_template' => 'module/payrec/tmpl/method-' . $pmethod->mid,
                    '_data' => array (
                        'tran' => $tran,
                        'method' => $pmethod,
                    ),
                ),
            ),
        );
        
        $form->items['info'] = array (
            '_display' => $allow_verify || $tran->status > 0,
            '_label' => 'Check details',
            '_widget' => 'container',
        );
        $form->items['info']['_data']['bankName'] = array (
            '_label' => 'Bank name',
            '_widget' => 'input',
            '_default' => $check_data['bankName'],
            'maxlength' => 255,
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['ccode'] = array (
            '_label' => 'Check number',
            '_widget' => 'input',
            '_default' => $check_data['ccode'],
            'maxlength' => 16,
            'class' => array ('size-small'),
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['amount'] = array (
            '_label' => 'Amount',
            '_widget' => 'input',
            '_default' => $check_data['amount'],
            '_suffix' => $tran->idCurrency,
            'type' => 'number',
            'maxlength' => 20,
            'class' => array ('size-small'),
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['dtIssued'] = array (
            '_label' => 'Date issued',
            '_widget' => 'input',
            'type' => 'date',
            '_default' => $check_data['dtIssued'],
            'class' => array ('size-small'),
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['description'] = array (
            '_label' => 'Notes',
            '_description' => 'Other notes about the check, if any.',
            '_widget' => 'textarea',
            '_default' => $check_data['description'],
            'maxlength' => 1000,
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'dropdown',
            '_options' => array (
                -1 => 'Failed',
                0 => 'In progress',
                1 => 'Succeeded',
            ),
            '_default' => $tran->status,
            'readonly' => !$allow_verify,
        );
        
        // Can the user verify the transaction?
        if (!$allow_verify):
            $form->items['info']['_data']['status']['_display'] = FALSE;
        endif;
        
        $form->actions['save'] = array (
            '_display' => $allow_verify,
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url,
        );
        
        $form->onPrepare();
        
        // Validate form
        if ($form->isSubmitted()):
            
            $form_data = $form->getValues();
            
            // Update check data
            $check_data = natty_array_merge_intersection($check_data, $form_data);
            if ($check_data['amount'] > 0)
                self::writeCheckData($check_data);
        
            // Update transaction status
            if (isset ($form_data['status']) && 0 !== (int) $form_data['status']):
                $tran->dtVerified = date('Y-m-d h:i:s');
                $tran->status = $form_data['status'];
                $tran->save();
            endif;
            
            // Display message and refresh
            \Natty\Console::success();
            $form->onProcess();
            
        endif;
        
        // Attach form
        $build['form'] = $form->getRarray();
        
    }
    
    protected static function getDefaultCheckData() {
        return array (
            'ccode' => NULL,
            'tid' => NULL,
            'dtIssued' => NULL,
            'bankName' => NULL,
            'amount' => NULL,
            'description' => NULL,
        );
    }
    
    public static function readCheckData($tran_id) {
        
        $dbo = \Natty::getDbo();
        $record = $dbo->read('%__payrec_check_data', array (
            'key' => array ('tid' => $tran_id),
            'unique' => 1,
        ));
        
        if (!$record):
            $record = self::getDefaultCheckData();
            $record['tid'] = $tran_id;
        endif;
        
        return $record;
        
    }
    
    public static function writeCheckData(array &$record) {
        
        $defaults = self::getDefaultCheckData();
        $record = natty_array_merge_intersection($defaults, $record);
        
        // Must specify a transaction id
        if (!$record['tid'])
            throw new \InvalidArgumentException('Required property "tid" cannot be empty.');
        
        // Update or insert as needed
        $dbo = \Natty::getDbo();
        $dbo->upsert('%__payrec_check_data', $record, array (
            'keys' => array ('tid'),
        ));
        
    }
    
    public static function getDefaultSettings() {
        return array (
            'payeeName' => NULL,
            'payeeAddress' => NULL,
            'notes' => NULL,
        );
    }
    
}