<?php

namespace Module\Payrec\Logic;

class Backend_MethodController {
    
    public static function pageManage() {
        
        // Load dependencies
        $method_handler = \Natty::getHandler('payrec--method');
        $method_coll = $method_handler->read(array (
            'conditions' => array (
                array ('AND', '1=1'),
            ),
        ));
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => 'Type', 'class' => array ('size-medium')),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List body
        $list_body = array ();
        foreach ($method_coll as $method):
            
            $row = array ();
        
            $row['title'] = '<div class="prop-title">' . $method->name . '</div>';
            if ($method->description)
                $row['title'] .= '<div>' . $method->description . '</div>';
            
            $row[] = $method->type;
            
            $row['context-menu'] = $method->call('buildBackendLinks');
            
            $row = array (
                '_data' => $row,
                'class' => array (),
            );
            if (!$method->status)
                $row['class'][] = 'n-state-disabled';
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($method_id) {
        
        $method = \Natty::getEntity('payrec--method', $method_id, array (
            'language' => \Natty::getInputLangId(),
        ));
        
        // Bounce URL
        $bounce_url = \Natty::getCommand();
        $bounce_url = dirname($bounce_url);
        $bounce_url = \Natty::url($bounce_url);
        
        // Prepare
        $form = new \Natty\Form\FormObject(array (
            'id' => 'payment-method-form',
        ), array (
            'etid' => 'payrec--method',
            'entity' => &$method,
        ));
        
        $form->addListener(array ($method->helper, 'handleSettingsForm'));
        
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $method->name,
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_default' => $method->description,
        );
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $method->status,
            'class' => array ('options-inline'),
        );
        
        $form->items['settings'] = array (
            '_label' => 'Settings',
            '_widget' => 'container',
            '_data' => array (),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'href' => \Natty::url('backend/payments'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ($form->isSubmitted('save')):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ($form->isSubmitted('save') && $form->isValid()):
            
            $form_data = $form->getValues();
        
            $method->settings = array_merge($method->settings, $form_data['settings']);
            unset ($form_data['settings']);
            
            $method->setState($form_data);
            $method->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}