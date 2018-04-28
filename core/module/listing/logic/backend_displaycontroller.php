<?php

namespace Module\Listing\Logic;

class Backend_DisplayController {
    
    public static function pageManage($list) {
        
        // List head
        $list_head = array (
            array ('_data' => 'Name'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        $list_body = array ();
        foreach ( $list->visibility as $did => $display ):

            $row = array ();
            $row[] = '<div class="prop-title">' . $display['name'] . ($display['isLocked'] ? ' (Locked)' : '') . '</div>'
                    . '<div class="prop-description">' . $display['description'] . '</div>';
            $row['context-menu'] = array (
                '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did) . '">Configure</a>'
            );

            $list_body[] = $row;

        endforeach;

        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/create') . '" class="k-button">Create</a>',
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $list, $did) {
        
        // Load dependencies
        $list_handler = \Natty::getHandler('listing--list');
        $response = \Natty::getResponse();
        
        // Create
        if ( 'create' === $mode ) {
            
            $display = $list->createVisibility();
            $response->attribute('title', 'Create visibility');
            
        }
        // Edit
        else {
            
            if ( !isset ($list->visibility[$did]) )
                \Natty::error(404);
            
            $display = $list->readVisibility($did);
            $response->attribute('title', 'Configure ' . $list->name);
            
        }
        
        $bounce_url = \Natty::url('backend/listing/' . $list->lid . '/visibility');
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'listing-list-display-form',
        ));
        
        $fs =& $form->items['default']['_data'];
        $fs['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $display['name'],
        );
        
        $fs['id'] = array (
            '_widget' => 'iname',
            '_iname' => array (
                'base' => 'name',
            ),
            '_default' => $display['id'],
        );
        if ( 'edit' == $mode || $display['isLocked'] )
            $fs['id']['readonly'] = 1;
        
        $fs['description'] = array (
            '_widget' => 'input',
            '_default' => $display['description'],
            'maxwidth' => 255,
        );
        $fs['status'] = array (
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $display['status'],
            'maxwidth' => 255,
            'class' => array ('options-inline'),
        );
        
        // Render settings
        $form->items['render'] = array (
            '_widget' => 'container',
            '_label' => 'Rendering',
            '_data' => array (),
        );
        $fs =& $form->items['render']['_data'];
        
        // Render settings
        $form->items['render'] = array (
            '_widget' => 'container',
            '_label' => 'Rendering',
            '_data' => array (),
        );
        $fs =& $form->items['render']['_data'];
        $fs['renderType'] = array (
            '_label' => 'Presentation',
            '_widget' => 'dropdown',
            '_options' => array (
                'list' => 'List',
            ),
            '_default' => $display['renderType'],
        );
        $fs['renderMode'] = array (
            '_label' => 'View mode',
            '_widget' => 'dropdown',
            '_options' => array (
                'default' => 'Full',
                'preview' => 'Preview',
            ),
            '_default' => $display['renderMode'],
        );
        unset ($fs);
        
        // Filtering options
        $form->items['filtering'] = array (
            '_widget' => 'container',
            '_label' => 'Filtering',
            '_data' => array (),
        );
        $fs =& $form->items['filtering']['_data'];
        
        $fs['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/add/filter') . '" class="k-button">Create</a>'
            ),
        );
        
        $filtering_table = array (
            '_render' => 'table',
            '_head' => array (
                array ('_data' => 'Description'),
                array ('_data' => '', 'class' => array ('context-menu')),
            ),
            '_body' => array (),
        );
        foreach ( $display['filterData'] as $record_key => $record ):
            
            $record['actions'] = array ();
            $record['actions'][] = '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/filters/' . $record_key) . '">Edit</a>';
            $record['actions'][] = '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/filters/' . $record_key . '/delete') . '" data-ui-init="confirmation">Delete</a>';
            
            $row = array ();
            $row['title'] = '<div class="prop-title" title="' . $record['description'] . '">' . $record['name'] . ' ' . $record['method'] . ' ' . $record['operand'] . '</div>';
            $row['context-menu'] = $record['actions'];
            
            $filtering_table['_body'][] = $row;
            
        endforeach;
        
        $fs['table'] = $filtering_table;
        
        unset ($fs);
        
        // Sorting options
        $form->items['sorting'] = array (
            '_widget' => 'container',
            '_label' => 'Sorting',
            '_data' => array (),
        );
        $fs =& $form->items['sorting']['_data'];
        
        $fs['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/add/sort') . '" class="k-button">Create</a>'
            ),
        );
        
        $sorting_table = array (
            '_render' => 'table',
            '_head' => array (
                array ('_data' => 'Description'),
                array ('_data' => '', 'class' => array ('context-menu')),
            ),
            '_body' => array (),
        );
        
        foreach ( $display['sortData'] as $record_key => $record ):
            
            $record['actions'] = array ();
            $record['actions'][] = '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/sorts/' . $record_key) . '">Edit</a>';
            $record['actions'][] = '<a href="' . \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/sorts/' . $record_key . '/delete') . '" data-ui-init="confirmation">Delete</a>';
            
            $row = array ();
            $row[] = $record['name'] . ' (' . $record['method'] . ')';
            $row['context-menu'] = $record['actions'];
            
            $sorting_table['_body'][] = $row;
            
        endforeach;
        
        $fs['table'] = $sorting_table;
        
        unset ($fs);
        
        // Pager settings
        $form->items['pager'] = array (
            '_widget' => 'container',
            '_label' => 'Pager',
            '_data' => array (),
        );
        $fs =& $form->items['pager']['_data'];
        $fs['pagerStatus'] = array (
            '_widget' => 'options',
            '_label' => 'Pager status',
            '_default' => $display['pagerStatus'],
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            'type' => 'options',
            'class' => array ('options-inline'),
        );
        $fs['pagerLinks'] = array (
            '_widget' => 'input',
            '_label' => 'Pager links',
            '_description' => 'Number of pager links to display.',
            '_default' => $display['pagerLinks'],
            'type' => 'number',
            'min' => 0,
            'max' => 9,
            'class' => array ('widget-small'),
        );
        $fs['pagerOffset'] = array (
            '_widget' => 'input',
            '_label' => 'Offset',
            '_description' => 'Number of items to skip from the beginning.',
            '_default' => $display['pagerOffset'],
            'readonly' => 1,
            'type' => 'number',
            'min' => 0,
            'max' => 1000,
            'class' => array ('widget-small'),
        );
        $fs['pagerLimit'] = array (
            '_widget' => 'input',
            '_label' => 'Limit',
            '_description' => 'Number of items to display at a time. Set this at zero to show all items.',
            '_default' => $display['pagerLimit'],
            'type' => 'number',
            'min' => 0,
            'max' => 1000,
            'class' => array ('widget-small'),
        );
        unset ($fs);
        
        // Additional settings
        $form->items['misc'] = array (
            '_widget' => 'container',
            '_label' => 'Miscellaneous',
            '_data' => array (),
        );
        $fs =& $form->items['misc']['_data'];
        $fs['cssClass'] = array (
            '_label' => 'CSS Class',
            '_widget' => 'input',
            '_default' => $display['cssClass'],
            'type' => 'text',
            'maxlength' => 255,
        );
        unset ($fs);
        
        // Form actions
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
            
            natty_debug($form_data);
            
            $form->onProcess();
            
        endif;
        
        // Prepare output
        $output = $form->getRarray();
//        \Natty\Console::debug($display);
        return $output;
        
    }
    
    public static function pagePropertyList($list, $did, $purpose) {
        
        // Load dependencies
        $entity_handler = \Natty::getHandler($list->settings['etid']);
        $list_handler = \Natty::getHandler('listing--list');
        
        // Validate display
        if ( !$display = $list->readVisibility($did) )
            \Natty::error(400);
        
        // Validate purpose
        \Natty::getResponse()->attribute('title', 'Add ' . $purpose);
        
        // List head
        $list_head = array (
            array ('_data' => '', 'class' => array ('cont-checkbox')),
            array ('_data' => 'Description'),
        );
        $list_body = array ();
        
        $model_name = $entity_handler->getModelName();
        $model_name = ucfirst($model_name);
        
        // List entity properties
        foreach ( $entity_handler->getPropertyDefinition() as $code => $definition ):
            
            if ( !isset ($definition['label']) )
                $definition['label'] = $code;
            if ( !isset ($definition['description']) )
                $definition['description'] = 'No description.';
            
            $row = array ();
            $row[] = array (
                '_data' => '<input type="radio" name="poa" value="property-' . $code . '" id="property-' . $code . '" />',
                'class' => array ('n-ta-ce'),
            );
            $row[] = '<label class="prop-title" for="property-' . $code . '">' . $model_name . ': ' . $definition['label'] . '</label>'
                . '<div class="prop-description">' . $definition['description'] . '</div>';
            
            $list_body[] = $row;
            
        endforeach;
        
        // List form
        $list_form = array (
            '_actions' => array (
                '<input type="submit" name="submit" value="Continue" class="k-button k-primary" />'
            ),
        );
        
        // Handle submission
        $form_valid = FALSE;
        if ( isset ($_POST['submit']) ):
            
            $form_valid = TRUE;
            
            // Must have a property or attribute
            if ( !isset ($_POST['poa']) || !$_POST['poa'] ):
                \Natty\Console::error('Please choose an item to continue.');
                $form_valid = FALSE;
            endif;
            
        endif;
        
        // Process form
        if ( $form_valid ):
            
            $poa_parts = explode('-', $_POST['poa']);
            $item_defi = array (
                'nature' => $poa_parts[0],
                'code' => $poa_parts[1],
            );
            
            if ( 'property' === $poa_parts[0] ) {
                $prop_defi = $entity_handler->properties[$poa_parts[1]];
                $item_defi['tableName'] = isset ($prop_defi['isTranslatable'])
                        ? $entity_handler->i18nTableName
                        : $entity_handler->tableName;
                $item_defi['tableAlias'] = isset ($prop_defi['isTranslatable'])
                        ? $entity_handler->getModelCode() . '_i18n'
                        : $entity_handler->getModelCode();
                $item_defi['columnName'] = $poa_parts[1];
                $item_defi['dtid'] = isset ($prop_defi['type'])
                        ? $prop_defi['type'] : 'varchar';
                $item_defi['i18n'] = (int) isset ($prop_defi['isTranslatable']);
            }
            else {
                natty_debug();
            }
            
            switch ( $purpose ):
                case 'filter':
                    $item_id = sizeof($display['filterData']) + 1;
                    $display['filterData'][$item_id] = $item_defi;
                    $location = \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/filters/' . $item_id);
                    break;
                case 'sort':
                    $item_id = sizeof($display['sortData']) + 1;
                    $display['sortData'][$item_id] = $item_defi;
                    $location = \Natty::url('backend/listing/' . $list->lid . '/visibility/' . $did . '/sorts/' . $item_id);
                    break;
                default:
                    \Natty::error(400);
            endswitch;
            
            $list->writeVisibility($did, $display);
            $list->save();
            
            \Natty\Console::success();
            \Natty::getResponse()->redirect($location);
            
        endif;
        
        // Prepare response
        $output = array ();
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            '_form' => $list_form,
        );
        
        return $output;
        
    }
    
}