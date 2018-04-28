<?php

namespace Module\Commerce\Logic;

class Backend_TaskStatusController {
    
    public static function pageManage() {
        
        // Load dependencies
        $tstatus_controller = \Natty::getHandler('commerce--taskstatus');
        
        // List head
        $list_head = array (
            array ('_data' => '', 'class' => array ('size-xsmall')),
            array ('_data' => 'Description'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        $list_data = $tstatus_controller->read(array (
            'condition' => array (
                array ('AND', '{taskstatus}.{status} >= 0'),
            ),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data as $tstatus ):
            
            $row = array ();
        
            $row['color'] = array (
                'class' => array ('n-ta-ce'),
                '_data' => '<span class="n-swatch" style="background-color: ' . natty_vod($tstatus->colorCode, '#fff') . '" title="' . $tstatus->colorCode . '"></span>',
            );
        
            $row['title'] = '<div class="prop-title">' . $tstatus->name . ($tstatus->isLocked ? ' <i class="n-icon n-icon-lock"></i>' : '') . '</div>';
            if ($tstatus->description)
                $row['title'] .= '<div class="">' . $tstatus->description . '</div>';
            
            $row['context-menu'] = $tstatus->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/commerce/task-statuses/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        return $output;
        
    }
    
    public static function pageForm($mode, $tsid) {
        
        // Load dependencies
        $tstatus_handler = \Natty::getHandler('commerce--taskstatus');
        $response = \Natty::getResponse();
        
        // Create
        if ( 'create' === $mode ) {
            
            $tstatus = $tstatus_handler->create(array (
                'language' => \Natty::getInputLangId(),
            ));
            $response->attribute('title', 'Create task status');
            
        }
        // Edit
        else {
            
            $tstatus = $tstatus_handler->readById($tsid, array (
                'language' => \Natty::getInputLangId(),
            ));
            if (!$tstatus)
                \Natty::error(400);
            
            $response->attribute('title', 'Edit task status');
            
        }
        
        $bounce_url = \Natty::url('backend/commerce/task-statuses');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-taskstatus-form',
            'i18n' => TRUE,
        ), array (
            'etid' => 'commerce--taskstatus',
            'entity' => &$tstatus,
        ));
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $tstatus->name,
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'textarea',
            '_default' => $tstatus->description,
        );
        $form->items['default']['_data']['colorCode'] = array (
            '_label' => 'Color',
            '_widget' => 'input',
            '_default' => $tstatus->colorCode,
            'type' => 'color',
        );
        
        if ( $tstatus->isLocked ):
            $form->items['default']['_data']['name']['readonly'] = 1;
        endif;
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'href' => $bounce_url,
        );
        
        $form->onPrepare();
        
        // Validate form
        if ($form->isSubmitted('save')):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ($form->isSubmitted('save') && $form->isValid()):
            
            $form_data = $form->getValues();
            $tstatus->setState($form_data);
            
            $tstatus->save();
            \Natty\Console::success();
            
            $form->redirect = $bounce_url;
            $form->onProcess();
            
        endif;
        
        // Prepare response
        return $form->getRarray();
        
    }
    
    public static function actionDelete($tstatus) {
        
        natty_debug('How to handle delete?');
        
    }
    
}