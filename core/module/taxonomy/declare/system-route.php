<?php

defined('NATTY') or die;

$data['backend/taxonomy'] = array (
    'module' => 'taxonomy',
    'heading' => 'Taxonomies',
    'description' => 'Manage taxonomy groups and taxonomy terms.',
    'contentCallback' => 'taxonomy::Backend_GroupController::pageManage',
    'permArguments' => array ('taxonomy--administer'),
    'parentId' => 'dashboard/features',
    'isBackend' => 1,
);
$data['backend/taxonomy/create'] = array (
    'module' => 'taxonomy',
    'heading' => 'Create',
    'contentCallback' => 'taxonomy::Backend_GroupController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('taxonomy--administer'),
    'isBackend' => 1,
);

// Read taxonomy groups
$tgroup_handler = \Natty::getHandler('taxonomy--group');
$tgroup_coll = $tgroup_handler->read(array (
    'ordering' => array ('name' => 'asc'),
));

foreach ( $tgroup_coll as $tgroup ):
    
    $data['backend/taxonomy/' . $tgroup->gcode] = array (
        'module' => 'taxonomy',
        'heading' => 'Edit',
        'wildcardType' => array (
            2 => 'taxonomy--group'
        ),
        'contentCallback' => 'taxonomy::Backend_GroupController::pageForm',
        'contentArguments' => array ('edit', 2),
        'permArguments' => array ('taxonomy--administer'),
        'parentId' => 'backend/taxonomy',
        'isBackend' => 1,
    );
    $data['backend/taxonomy/' . $tgroup->gcode . '/delete'] = array (
        'module' => 'taxonomy',
        'heading' => 'Delete',
        'contentCallback' => 'taxonomy::Backend_GroupController::pageAction',
        'contentArguments' => array (3, 2),
        'permArguments' => array ('taxonomy--administer'),
        'parentId' => 'backend/taxonomy',
        'isBackend' => 1,
    );
    $data['backend/taxonomy/' . $tgroup->gcode . '/terms'] = array (
        'module' => 'taxonomy',
        'heading' => $tgroup->name . ': Terms',
        'wildcardType' => array (
            2 => 'taxonomy--group',
        ),
        'contentCallback' => 'taxonomy::Backend_TermController::pageManage',
        'contentArguments' => array (2),
        'permArguments' => array ('taxonomy--administer'),
        'isBackend' => 1,
    );
    $data['backend/taxonomy/' . $tgroup->gcode . '/terms/create'] = array (
        'module' => 'taxonomy',
        'heading' => 'Create',
        'wildcardType' => array (
            2 => 'taxonomy--group',
        ),
        'contentCallback' => 'taxonomy::Backend_TermController::pageForm',
        'contentArguments' => array ('create', NULL, 2),
        'permArguments' => array ('taxonomy--administer'),
        'isBackend' => 1,
    );
    $data['backend/taxonomy/' . $tgroup->gcode . '/terms/%'] = array (
        'module' => 'taxonomy',
        'heading' => 'Edit',
        'wildcardType' => array (
            2 => 'taxonomy--group',
            4 => 'taxonomy--term',
        ),
        'contentCallback' => 'taxonomy::Backend_TermController::pageForm',
        'contentArguments' => array ('edit', 4, 2),
        'permArguments' => array ('taxonomy--administer'),
        'isBackend' => 1,
    );
    $data['backend/taxonomy/' . $tgroup->gcode . '/terms/%/delete'] = array (
        'module' => 'taxonomy',
        'wildcardType' => array (
            2 => 'taxonomy--group',
            4 => 'taxonomy--term',
        ),
        'contentCallback' => 'taxonomy::Backend_TermController::actionDelete',
        'contentArguments' => array (4),
        'permArguments' => array ('taxonomy--administer'),
        'isBackend' => 1,
    );
    $data['taxonomy/' . $tgroup->gcode] = array (
        'module' => 'taxonomy',
        'heading' => $tgroup->name,
        'wildcardType' => array (
            1 => 'taxonomy--term',
        ),
        'contentCallback' => 'taxonomy::Frontend_TermController::pageBrowse',
        'contentArguments' => array (1, NULL),
        'permArguments' => array (),
    );
    
endforeach;