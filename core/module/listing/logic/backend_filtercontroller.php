<?php

namespace Module\Listing\Logic;

use \Module\Listing\Classes\DatatypeHandler as DatatypeHandler;

class Backend_FilterController {
    
    public static function pageForm($list, $display_id, $filter_id = NULL) {
        
        if ( !$display = $list->readVisibility($display_id) )
            \Natty::error(400);
        
        if ( !isset ($display['filterData'][$filter_id]) )
            \Natty::error(400);
        $filter_data = $display['filterData'][$filter_id];
        
        // Load dependencies
        $datatype = DatatypeHandler::readById($filter_data['dtid']);
        if ( !$datatype )
            \Natty::error(500);
        
        $bounce_url = \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $display_id);
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'listing-list-filter-form',
        ), array (
            'list' => $list,
            'fid' => $filter_id,
        ));
        
        $form->addListener(array ($datatype->helper, 'handleFilterSettingsForm'));
        
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $filter_data['name'],
            '_ignore' => 1,
            'maxlength' => 255,
            'readonly' => 1,
        );
        $form->items['default']['_data']['poa'] = array (
            '_label' => 'Where',
            '_widget' => 'markup',
            '_markup' => $filter_data['name'],
            '_ignore' => 1,
        );
        $form->items['default']['_data']['method'] = array (
            '_widget' => 'dropdown',
            '_options' => array (
                '=' => 'Is equal to',
                '!=' => 'Is not equal to',
            ),
            '_default' => $filter_data['method'],
        );
        $form->items['default']['_data']['operand'] = array (
            '_widget' => 'input',
            '_default' => $filter_data['operand'],
            'type' => 'text',
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_description' => 'A note to remind you what this filter is about.',
            '_widget' => 'input',
            '_default' => $filter_data['description'],
            'maxlength' => 255,
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
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $display['filterData'][$filter_id] = array_merge($display['filterData'][$filter_id], $form_data);
            $list->writeVisibility($display_id, $display);
            $list->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        \Natty::getResponse()->attribute('title', 'Filter ' . $filter_data['name']);
        
        return $form->getRarray();
        
    }
    
    public static function actionDelete($list, $did, $fid) {
        
        if ( !isset ($list->visibility[$did]) )
            \Natty::error(400);
        $display =& $list->visibility[$did];
        
        if ( isset ($display['filterData'][$fid]) ):
            unset ($display['filterData'][$fid]);
            $list->save();
        endif;
        
        \Natty\Console::success();
        \Natty::getResponse()->bounce();
        
    }
    
}