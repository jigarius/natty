<?php

defined('NATTY') or die;

$data['backend/payrec/methods'] = array (
    'module' => 'payrec',
    'heading' => 'Payments & Receipts',
    'description' => 'Manage ways in which you pay or receive money on your site.',
    'contentCallback' => 'payrec::Backend_MethodController::pageManage',
    'permArguments' => array ('system--administer'),
    'parentId' => 'dashboard/settings',
    'isBackend' => 1,
);
$data['backend/payrec/methods/%'] = array (
    'module' => 'payrec',
    'heading' => 'Configure',
    'contentCallback' => 'payrec::Backend_MethodController::pageForm',
    'contentArguments' => array (3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

$data['backend/payrec/trans'] = array (
    'module' => 'payrec',
    'heading' => 'Transactions',
    'contentCallback' => 'payrec::Backend_TranController::pageManage',
    'permCallback' => 'system::EntityController::routePermCallback',
    'permArguments' => array (3, 'view'),
    'isBackend' => 1,
);
$data['backend/payrec/trans/%'] = array (
    'module' => 'payrec',
    'wildcardType' => array (
        3 => 'payrec--tran'
    ),
    'heading' => 'View',
    'contentCallback' => 'payrec::Backend_TranController::pageView',
    'contentArguments' => array (3),
    'permCallback' => 'system::EntityController::routePermCallback',
    'permArguments' => array (3, 'view'),
    'isBackend' => 1,
);