<?php

namespace Module\Location\Logic;

class Backend_UserAddressController {
    
    public static function pageManage($user) {
        
        if ( 'self' === $user )
            $user = \Natty::getUser();
        
        // Load dependencies
        $address_handler = \Natty::getHandler('location--useraddress');
        
        // List head
        $list_head = array (
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List body
        $address_coll = $address_handler->read(array (
            'key' => array (
                'idUser' => $user->uid,
            ),
        ));
        $list_body = array ();
        foreach ( $address_coll as $address ):
            
            $row = array ();
            $row[] = '<div class="prop-title">' . $address->name . '</div>'
                    . '<div class="prop-description">' . nl2br($address->body) . '</div>';
            $row['context-menu'] = $address->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                'create' => array (
                    '_render' => 'element',
                    '_element' => 'a',
                    '_data' => 'Create',
                    'href' => \Natty::url('backend/people/users/' . $user->uid . '/addresses/create'),
                    'class' => array ('k-button'),
                )
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $address = NULL, $user = NULL) {
        
        // Load dependencies
        $address_handler = \Natty::getHandler('location--useraddress');
        $response = \Natty::getResponse();
        $auth_user = \Natty::getUser();
        
        // Create
        if ( 'create' === $mode ) {
            $address = $address_handler->create(array (
                'idUser' => $user->uid,
            ));
            $response->attribute('title', 'Create address');
        }
        // Edit
        else {
            $response->attribute('title', 'Edit address');
        }
        
        // Validate address type
        if ( $address->idUser != $user->uid )
            \Natty::error(400);
        
        // Bounce URL
        $bounce_url = \Natty::getRequest()->getString('bounce');
        if ( !$bounce_url )
            $bounce_url = \Natty::url('backend/people/users/' . $user->uid . '/addresses');
        
        // Validate permission
        if ( $user->uid != $auth_user->uid && !$auth_user->can('location--manage any address entities') )
            \Natty::error(403);
        
        // Country options
        $country_opts = \Natty::getHandler('location--country')->readOptions(array (
            'key' => array ('status' => 1),
        ));
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'location-address-form',
        ));
        $form->items['default']['_data']['body'] = array (
            '_label' => 'Street address',
            '_description' => 'Building name / number, street name, locality.',
            '_widget' => 'textarea',
            '_default' => $address->body,
        );
        $form->items['default']['_data']['landmark'] = array (
            '_label' => 'Landmark',
            '_description' => 'Example: Near airport gate number 4.',
            '_widget' => 'input',
            '_default' => $address->landmark,
            'maxlength' => 255,
        );
        $form->items['default']['_data']['city'] = array (
            '_label' => 'City',
            '_description' => 'Name of your city or district.',
            '_widget' => 'input',
            '_default' => $address->city,
            'maxlength' => 255,
        );
        $form->items['default']['_data']['postCode'] = array (
            '_label' => 'Postal code',
            '_description' => 'Enter the postal code as it is used in your country/region.',
            '_widget' => 'input',
            '_default' => $address->postCode,
            'maxlength' => 16,
            'required' => 1,
        );
        $form->items['default']['_data']['cid'] = array (
            '_label' => 'Country',
            '_widget' => 'dropdown',
            '_options' => $country_opts,
            '_default' => $address->cid,
            'id' => 'fw-cid',
            'required' => 1,
        );
        $form->items['default']['_data']['sid'] = array (
            '_label' => 'State',
            '_widget' => 'dropdown',
            '_options' => array (),
            '_default' => $address->sid,
            'data-ui-init' => array ('state-picker'),
            'data-state-picker-country-picker' => '#fw-cid',
            'required' => 1,
        );
        
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_description' => 'Enter a name this address to help you recognize it. Example: Riverside Home or Bella  Apartment.',
            '_widget' => 'input',
            '_default' => $address->name,
            'maxlength' => 255,
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save'
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url($bounce_url),
        );
        
        $form->scripts[] = array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'location') . '/reso/state-picker.js'
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isSubmitted('save') ):
            
            $form_data = $form->getValues();
            $address->setState($form_data);
            $address->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Build response
        $output = array ();
        $output['form'] = $form->getRarray();
        
        return $output;
        
    }
    
    public static function pageAction($do, $with = NULL) {
        
        switch ($do):
            case 'delete':
                
                if ( !$with->call('allowAction', 'delete') )
                    \Natty::error(403);
                
                $with->delete();
                \Natty\Console::success();
                
                break;
            default:
                \Natty::error(403);
                break;
        endswitch;
        
        \Natty::getResponse()->bounce();
        
    }
    
}