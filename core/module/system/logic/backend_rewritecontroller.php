<?php

namespace Module\System\Logic;

class Backend_RewriteController {
    
    public static function pageManage() {
        
        $rewrite_handler = \Natty::getHandler('system--rewrite');
        
        $list_head = array (
            array ('_data' => 'System URL'),
            array ('_data' => 'Custom URL'),
            array ('_data' => 'Language', 'width' => 120),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        $query = \Natty::getDbo()
                ->getQuery('select', '%__system_rewrite r')
                ->addComplexCondition('AND', '{ail} = :ail');
        $pager = new \Natty\Helper\PagingHelper($query);
        $list_data = $pager->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
        ));
        $list_body = array ();
        
        foreach ( $list_data['items'] as $rewrite ):
            
            $rewrite = $rewrite_handler->create($rewrite);
            
            $row = array ();
            $row[] = $rewrite->systemPath;
            $row[] = $rewrite->customPath;
            $row[] = $rewrite->ail;
            $row['context-menu'] = $rewrite->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/system/rewrites/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode = NULL, $entity = NULL) {
        
        $rewrite_handler = \Natty::getHandler('system--rewrite');
        
        if ( 'create' === $mode ) {
            $entity = $rewrite_handler->create(array (
                'ail' => \Natty::getOutputLangId(),
            ));
        }
        else {
            
        }
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'system-rewrite-form',
        ));
        
        $form->items['default']['_data']['ail'] = array (
            '_label' => 'Language ID',
            '_widget' => 'input',
            '_default' => $entity->ail,
            'maxlength' => 8,
            'readonly' => 1,
        );
        $form->items['default']['_data']['systemPath'] = array (
            '_label' => 'System Path',
            '_widget' => 'input',
            '_default' => $entity->systemPath,
            'maxlength' => 255,
        );
        $form->items['default']['_data']['customPath'] = array (
            '_label' => 'Custom Path',
            '_widget' => 'input',
            '_default' => $entity->customPath,
            'maxlength' => 255,
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['list'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/system/rewrites'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_values = $form->getValues();
            
            // System URL must be valid
            $route = \Natty::command2route($form_values['systemPath']);
            if ( !$route ):
                $form->items['default']['_data']['systemPath']['_errors'][] = 'The path "' . $form_values['systemPath'] . '" could not be recognized.';
                $form->isValid(FALSE);
            endif;
        
            // Custom URL must not be duplicate
            $conflict = $rewrite_handler->read(array (
                'key' => array (
                    'ail' => $form_values['ail'],
                    'customPath' => $form_values['customPath'],
                ),
            ));
            if ( $conflict ):
                $form->items['default']['_data']['customPath']['_errors'][] = 'The path "' . $form_values['customPath'] . '" is already in use.';
                $form->isValid(FALSE);
            endif;
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $entity->setState($form_values);
            $entity->save();
            
            \Natty\Console::message(NATTY_ACTION_SUCCEEDED);
            $form->redirect = \Natty::url('backend/system/rewrites');
            
            $form->onProcess();
            
        endif;
        
        return $form->getRarray();
        
    }
    
    public static function pageAction() {
        
        $request = \Natty::getRequest();
        $action = $request->getString('do');
        $entity = $request->getEntity('with', 'system--rewrite');
        
        switch ( $action ):
            case 'delete':
                
                $entity->delete();
                \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
                
                break;
            default:
                \Natty\Console::error(NATTY_ACTION_UNRECOGNIZED);
                break;
        endswitch;
        
        $location = \Natty::url('backend/system/rewrites');
        \Natty::getResponse()->bounce($location);
        
    }
    
}