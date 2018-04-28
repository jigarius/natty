<?php

defined('NATTY') or die;

// Attribute management
$data['backend/eav/attribute'] = array (
    'module' => 'eav',
    'heading' => 'Entity Attributes',
    'description' => 'Manage entity attributes and see usage information for the same.',
    'contentCallback' => 'eav::Backend_AttributeController::pageManage',
    'permArguments' => array ('eav--manage attribute entities'),
    'parentId' => 'dashboard/settings/technical',
    'isBackend' => 1,
);
$data['backend/eav/attribute/create'] = array (
    'module' => 'eav',
    'heading' => 'Create',
    'description' => 'Choose the type of attribute you wish to create.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);

// Read datatypes
$datatypes = Module\Eav\Controller::readDataTypes(TRUE);
foreach ( $datatypes as $datatype ):
    $rid = 'backend/eav/attribute/create/' . $datatype->dtid;
    $data[$rid] = array (
        'module' => 'eav',
        'heading' => 'Create ' . $datatype->name,
        'description' => $datatype->description,
        'wildcardType' => array (
            4 => 'eav--datatype',
        ),
        'contentCallback' => 'eav::Backend_AttributeController::pageForm',
        'contentArguments' => array ('create', 4, NULL),
        'permArguments' => array ('eav--manage attribute entities'),
        'isBackend' => 1,
    );
    unset ($rid, $datatype);
endforeach;
unset ($datatypes);

$data['backend/eav/attribute/%'] = array (
    'module' => 'eav',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'eav--attribute',
    ),
    'contentCallback' => 'eav::Backend_AttributeController::pageForm',
    'contentArguments' => array ('edit', 3, NULL),
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);

// Attribute instance management
$data['backend/eav/attr-inst/%'] = array (
    'module' => 'eav',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'eav--attrinst'
    ),
    'contentCallback' => 'eav::Backend_AttrInstController::pageForm',
    'contentArguments' => array ('edit', 3, NULL),
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);
$data['backend/eav/attr-inst/%/delete'] = array (
    'module' => 'eav',
    'heading' => 'Delete',
    'wildcardType' => array (
        3 => 'eav--attrinst',
    ),
    'contentCallback' => 'eav::Backend_AttrInstController::actionDelete',
    'contentArguments' => array (3),
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);
$data['backend/eav/attr-inst/%/display'] = array (
    'module' => 'eav',
    'heading' => 'Display',
    'wildcardType' => array (
        3 => 'eav--attrinst'
    ),
    'contentCallback' => 'system::DefaultController::pageDashboard',
    'contentArguments' => array (3, 'default'),
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);
$data['backend/eav/attr-inst/%/display/%'] = array (
    'module' => 'eav',
    'heading' => 'Display',
    'wildcardType' => array (
        3 => 'eav--attrinst',
    ),
    'contentCallback' => 'eav::Backend_AttrInstDisplayController::pageForm',
    'contentArguments' => array (3, 5),
    'permArguments' => array ('eav--manage attribute entities'),
    'isBackend' => 1,
);

// Entity reference suggestions
//$data['eav/attr-inst/%/read-suggestions'] = array (
//    'module' => 'eav',
//    'wildcardType' => array (
//        2 => 'eav--attrinst',
//    ),
//    'contentCallback' => 'eav::attrinst/read-suggestions',
//    'contentArguments' => array (2),
//    'permArguments' => array ('eav--manage attribute entities'),
//    'isBackend' => 1,
//);

// Read entity types and groups
$etypes = Module\System\Controller::readEntityTypes(TRUE);
foreach ( $etypes as $etid => $etype ):
    
    // Entity must be attributable
    if ( !$etype->isAttributable )
        continue;
    
    // Build routes for eav pages
    $entity_handler = \Natty::getHandler($etid);
    $egroup_data = $entity_handler->getEntityGroupData();

    if ( !is_array($egroup_data) || 0 === sizeof($egroup_data) )
        continue;
    
    foreach ( $egroup_data as $egid => $egroup ):
        
        $egroup_form_uri = $egroup['uri'];
        $egroup_list_uri = dirname($egroup_form_uri);
        
        // Attribute management
        $data[$egroup_form_uri . '/attribute'] = array (
            'module' => 'eav',
            'contentCallback' => 'eav::Backend_AttrInstController::pageManage',
            'contentArguments' => array ($etid, strval($egid), $egroup_list_uri),
            'heading' => $egroup['name'] . ': Attributes',
            'permArguments' => array ( $entity_handler->getModuleCode() . '/manage ' . $entity_handler->getModelCode()),
            'isBackend' => 1,
        );
        
        // Display management
        $data[$egroup_form_uri . '/display'] = array (
            'module' => 'eav',
            'contentCallback' => 'system::DashboardController::pageDashboard',
            'heading' => natty_text('[@entitytype]: View modes', array (
                'entitytype' => $etype->name,
            )),
            'permArguments' => array ('eav--manage attribute entities'),
            'isBackend' => 1,
        );
        
        foreach ( $etype->viewModes as $view_mode => $view_data ):
            
            $view_url = $egroup_form_uri . '/display/' . $view_mode;
            
            $data[$view_url] = array (
                'module' => 'eav',
                'contentCallback' => 'eav::Backend_AttrInstDisplayController::pageManage',
                'contentArguments' => array ($etid, strval($egid), $view_mode, $egroup_list_uri),
                'heading' => $view_data['name'],
                'description' => 'Configure how attributes would be displayed in <em>' . $view_mode . '</em> view mode.',
                'permArguments' => array ('eav--manage attribute entities'),
                'isBackend' => 1,
            );
                
        endforeach;
        
    endforeach;
    
endforeach;

