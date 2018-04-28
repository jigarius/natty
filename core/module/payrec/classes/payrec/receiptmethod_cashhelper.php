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
                $form->items['settings']['_data']['settings.notes'] = array (
                    '_label' => 'Notes',
                    '_description' => 'Example: Tender exact change for orders below $100.',
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
        
        // Show message
        $message = 'Payment for ' . $tran->contextLabel . ' shall be collected in cash.';
        \Natty\Console::message($message);
        
        // Redirect
        \Natty::getResponse()->redirect($tran->contextUrl);
        
    }
    
    public static function attachView($tran, array &$build) {
        
        $pmethod = \Natty::getEntity('payrec--method', $tran->mid);
        $allow_verify = $tran->call('allowAction', 'verify') && (0 === (int) $tran->status);
        
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
            '_label' => 'Payment details',
            '_widget' => 'container',
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
    
    public static function getDefaultSettings() {
        return array (
            'payeeName' => NULL,
            'payeeAddress' => NULL,
            'notes' => NULL,
        );
    }
    
}