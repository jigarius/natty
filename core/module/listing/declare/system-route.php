<?php

defined('NATTY') or die;

// Back end pages
$data['backend/listing'] = array (
    'module' => 'listing',
    'heading' => 'Entity Lists',
    'description' => 'Setup and display customized lists of entities on your websites.',
    'contentCallback' => 'listing::Backend_ListController::pageManage',
    'permArguments' => array ('listing--administer'),
    'parentId' => 'dashboard/features',
    'isBackend' => TRUE,
);
$data['backend/listing/create'] = array (
    'module' => 'listing',
    'heading' => 'Create',
    'contentCallback' => 'listing::Backend_ListController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/import'] = array (
    'module' => 'listing',
    'heading' => 'Import',
    'contentCallback' => 'listing::Backend_ListController::pageImport',
    'contentArguments' => array (),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%'] = array (
    'module' => 'listing',
    'heading' => 'Edit',
    'contentCallback' => 'listing::Backend_ListController::pageForm',
    'contentArguments' => array ('edit', 2),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%/visibility'] = array (
    'module' => 'listing',
    'heading' => 'Visibility',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_DisplayController::pageManage',
    'contentArguments' => array (2),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%/visibility/create'] = array (
    'module' => 'listing',
    'heading' => 'Create',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_DisplayController::pageForm',
    'contentArguments' => array ('edit', 2, NULL),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%/visibility/%'] = array (
    'module' => 'listing',
    'heading' => 'Configure',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_DisplayController::pageForm',
    'contentArguments' => array ('edit', 2, 4),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);

$data['backend/listing/%/visibility/%/add/%'] = array (
    'module' => 'listing',
    'heading' => 'Add item',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_DisplayController::pagePropertyList',
    'contentArguments' => array (2, 4, 6),
    'permArguments' => array ('listing--administer'),
    'isBackend' => 1,
);

$data['backend/listing/%/visibility/%/filters/%'] = array (
    'module' => 'listing',
    'heading' => 'Edit',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_FilterController::pageForm',
    'contentArguments' => array (2, 4, 6),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%/visibility/%/filters/%/delete'] = array (
    'module' => 'listing',
    'heading' => 'Delete',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_FilterController::actionDelete',
    'contentArguments' => array (2, 4, 6),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);

$data['backend/listing/%/visibility/%/sorts/%'] = array (
    'module' => 'listing',
    'heading' => 'Edit',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_SortController::pageForm',
    'contentArguments' => array (2, 4, 6),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);
$data['backend/listing/%/visibility/%/sorts/%/delete'] = array (
    'module' => 'listing',
    'heading' => 'Delete',
    'wildcardType' => array (
        2 => 'listing--list',
    ),
    'contentCallback' => 'listing::Backend_SortController::actionDelete',
    'contentArguments' => array (2, 4, 6),
    'permArguments' => array ('listing--administer'),
    'isBackend' => TRUE,
);

// Front end pages
$list_coll = \Natty::getHandler('listing--list')->read();
foreach ( $list_coll as $list ):
    
    foreach ( $list->visibility as $did => $display ):
    
        // Create pages
        if ( 'page' === $display['type'] ):
            
            // Must specify path for page
            $page_path = $display['settings']['path'];
            if ( !$page_path )
                continue;
            
            // Path must be free
            if ( isset ($data[$page_path]) ):
                \Natty\Console::message('Could not attach entity list to the URL "' . $page_path . '". The URL is already in use and was left untouched.');
                continue;
            endif;
            
            // Create the route entry
            $data[$page_path] = array (
                'module' => 'listing',
                'heading' => $display['name'],
                'description' => $display['description'],
                'contentCallback' => 'listing::Frontend_ListController::pageViewList',
                'contentArguments' => array ($list->lid, $did),
                'permArguments' => array (),
            );
            
        endif;
    
    endforeach;
    
endforeach;