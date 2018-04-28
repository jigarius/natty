<?php

namespace Module\Payrec\Classes\Payrec;

class ReceiptMethod_WireHelper
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
                $form->items['settings']['_data']['settings.bankName'] = array (
                    '_label' => 'Bank name',
                    '_widget' => 'input',
                    'maxlength' => 255,
                    '_default' => $method->settings['bankName'],
                );
                $form->items['settings']['_data']['settings.bankInfo'] = array (
                    '_label' => 'Bank info',
                    '_description' => 'Enter other information like branch details, SWIFT code, etc.',
                    '_widget' => 'textarea',
                    '_default' => $method->settings['bankInfo'],
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
        $email = \Natty::getEntity('system--email', 'payrec--wire receipt pending');
        $email->send($email_options);
        
        // Redirect 
        $location = \Natty::url('backend/payrec/trans/' . $tran->tid);
        \Natty::getResponse()->redirect($location);
        
    }
    
    public static function attachView($tran, array &$build) {
        
        $pmethod = \Natty::getEntity('payrec--method', $tran->mid);
        $allow_verify = $tran->call('allowAction', 'verify') && (0 === (int) $tran->status);
        
        // Load existing check data
        $wire_data = self::readWireData($tran->tid);
        
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
                    '_render' => 'twig',
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
            '_label' => 'Wire details',
            '_widget' => 'container',
        );
        $form->items['info']['_data']['wcode'] = array (
            '_label' => 'Transaction ID',
            '_widget' => 'input',
            '_default' => $wire_data['wcode'],
            'maxlength' => 32,
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['amount'] = array (
            '_label' => 'Amount',
            '_widget' => 'input',
            '_default' => $wire_data['amount'],
            '_suffix' => $tran->idCurrency,
            'type' => 'number',
            'maxlength' => 20,
            'class' => array ('size-small'),
            'readonly' => !$allow_verify,
        );
        $form->items['info']['_data']['description'] = array (
            '_label' => 'Notes',
            '_description' => 'Other notes, if any.',
            '_widget' => 'textarea',
            '_default' => $wire_data['description'],
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
            $wire_data = natty_array_merge_intersection($wire_data, $form_data);
            if ($wire_data['amount'] > 0)
                self::writeWireData($wire_data);
        
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
    
    protected static function getDefaultWireData() {
        return array (
            'tid' => NULL,
            'wcode' => NULL,
            'amount' => NULL,
            'description' => NULL,
        );
    }
    
    public static function readWireData($tran_id) {
        
        $dbo = \Natty::getDbo();
        $record = $dbo->read('%__payrec_wire_data', array (
            'key' => array ('tid' => $tran_id),
            'unique' => 1,
        ));
        
        if (!$record):
            $record = self::getDefaultWireData();
            $record['tid'] = $tran_id;
        endif;
        
        return $record;
        
    }
    
    public static function writeWireData(array &$record) {
        
        $defaults = self::getDefaultWireData();
        $record = natty_array_merge_intersection($defaults, $record);
        
        // Must specify a transaction id
        if (!$record['tid'])
            throw new \InvalidArgumentException('Required property "tid" cannot be empty.');
        
        // Update or insert as needed
        $dbo = \Natty::getDbo();
        $dbo->upsert('%__payrec_wire_data', $record, array (
            'keys' => array ('tid'),
        ));
        
    }
    
    public static function getDefaultSettings() {
        return array (
            'payeeName' => NULL,
            'bankName' => NULL,
            'bankInfo' => NULL,
        );
    }
    
}