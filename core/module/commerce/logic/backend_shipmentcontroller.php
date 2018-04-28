<?php

namespace Module\Commerce\Logic;

class Backend_ShipmentController {
    
    public static function pageForm($mode, $shipment, $order) {
        
        // Load dependencies
        $shipment_handler = \Natty::getHandler('commerce--shipment');
        $response = \Natty::getResponse();
        
        // Create
        if ('create' === $mode) {
            $shipment = $shipment_handler->create(array (
                'oid' => $order->oid,
                'cid' => $order->idCarrier,
            ));
            $response->attribute('title', 'Create shipment');
        }
        // Edit
        else {
            $response->attribute('edit', 'Edit shipment');
        }
        
        // Bounce URL
        $bounce_url = \Natty::getRequest()->getString('bounce');
        if (!$bounce_url)
            $bounce_url = \Natty::url('backend/commerce/orders/' . $order->oid . '/shipments');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-shipment-form',
        ), array (
            'etid' => 'commerce--shipment',
            'entity' => $shipment,
        ));
        $form->items['default']['_label'] = 'Shipment information';
        $form->items['default']['_data']['scode'] = array (
            '_label' => 'Code',
            '_description' => 'Airway bill number or tracking code (if any).',
            '_widget' => 'input',
            '_default' => $shipment->scode,
            'maxlength' => 20,
            'class' => array ('size-small'),
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Details',
            '_description' => 'Service provider and other information.',
            '_widget' => 'input',
            '_default' => $shipment->description,
            'maxlength' => 255,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                -1 => 'Failed',
                0 => 'Dispatched',
                1 => 'Delivered'
            ),
            '_default' => $shipment->status,
            'class' => array ('options-inline'),
        );
        
        $form->actions['save'] = array (
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
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ($form->isValid()):
            
            $form_data = $form->getValues();
            $shipment->setState($form_data);
            $shipment->save();
            
            \Natty\Console::success();
            
            $form->redirect = $bounce_url;
            $form->onProcess();
            
        endif;
        
        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}