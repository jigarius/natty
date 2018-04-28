<?php

namespace Module\Listing\Logic;

use \Module\Listing\Classes\DatatypeHandler as DatatypeHandler;

class Backend_SortController {
    
    public static function pageForm($list, $display_id, $sort_id = NULL) {
        
        if ( !$display = $list->readVisibility($display_id) )
            \Natty::error(400);
        
        if ( !isset ($display['sortData'][$sort_id]) )
            \Natty::error(400);
        $sort_data = $display['sortData'][$sort_id];
        
        // Load dependencies
        $datatype = DatatypeHandler::readById($sort_data['dtid']);
        if ( !$datatype )
            \Natty::error(500);
        
        $bounce_url = \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $display_id);
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'listing-list-sort-form',
        ), array (
            'list' => $list,
            'sid' => $sort_id,
        ));
        
        $form->addListener(array ($datatype->helper, 'handleSortSettingsForm'));
        
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $sort_data['name'],
            '_ignore' => 1,
            'maxlength' => 255,
            'readonly' => 1,
        );
        $form->items['default']['_data']['method'] = array (
            '_widget' => 'dropdown',
            '_options' => array (
                'asc' => 'Ascending',
                'desc' => 'Descending',
            ),
            '_default' => $sort_data['method'],
        );
        $form->items['default']['_data']['description'] = array (
            '_label' => 'Description',
            '_description' => 'A note to remind you what this sort is about.',
            '_widget' => 'input',
            '_default' => $sort_data['description'],
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
            
            $display['sortData'][$sort_id] = array_merge($display['sortData'][$sort_id], $form_data);
            $list->writeVisibility($display_id, $display);
            $list->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        \Natty::getResponse()->attribute('title', 'Sort ' . $sort_data['name']);
        
        return $form->getRarray();
        
    }
    
    public static function actionDelete($list, $did, $sid) {
        
        if ( !isset ($list->visibility[$did]) )
            \Natty::error(400);
        $display =& $list->visibility[$did];
        
        if ( isset ($display['sortData'][$sid]) ):
            unset ($display['sortData'][$sid]);
            $list->save();
        endif;
        
        \Natty\Console::success();
        \Natty::getResponse()->bounce();
        
    }
    
}