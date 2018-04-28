<?php

defined('NATTY') or die;

$data['dashboard/features/commerce'] = array (
    'module' => 'commerce',
    'heading' => 'Commerce',
    'description' => 'Options related to the functionality of your commerce platform.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);
$data['dashboard/settings/commerce'] = array (
    'module' => 'commerce',
    'heading' => 'Commerce',
    'description' => 'Settings related to the functionality of your commerce platform.',
    'contentCallback' => 'commerce::DefaultController::pageSettings',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

// Tax management
$data['backend/commerce/taxes'] = array (
    'module' => 'commerce',
    'heading' => 'Taxes',
    'description' => 'Manage taxes levied on your products and services.',
    'contentCallback' => 'commerce::Backend_TaxController::pageManage',
    'permArguments' => array ('commerce--manage tax entities'),
    'parentId' => 'dashboard/features/commerce',
    'isBackend' => 1,
);
$data['backend/commerce/taxes/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'contentCallback' => 'commerce::Backend_TaxController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/taxes/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'contentCallback' => 'commerce::Backend_TaxController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/taxes/action'] = array (
    'module' => 'commerce',
    'contentCallback' => 'commerce::Backend_TaxController::pageAction',
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);

// Tax group management
$data['backend/commerce/tax-groups'] = array (
    'module' => 'commerce',
    'heading' => 'Tax groups',
    'description' => 'Manage tax groups levied on your products and services.',
    'contentCallback' => 'commerce::Backend_TaxGroupController::pageManage',
    'permArguments' => array ('commerce--manage tax entities'),
    'parentId' => 'dashboard/features/commerce',
    'isBackend' => 1,
);
$data['backend/commerce/tax-groups/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'contentCallback' => 'commerce::Backend_TaxGroupController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/tax-groups/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'contentCallback' => 'commerce::Backend_TaxGroupController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/tax-groups/action'] = array (
    'module' => 'commerce',
    'contentCallback' => 'commerce::Backend_TaxGroupController::pageAction',
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);

// Tax rules
$data['backend/commerce/tax-groups/%/tax-rules'] = array (
    'module' => 'commerce',
    'heading' => 'Tax rules',
    'wildcardType' => array (
        3 => 'commerce--taxgroup',
    ),
    'contentCallback' => 'commerce::Backend_TaxRuleController::pageManage',
    'contentArguments' => array (3),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/tax-groups/%/tax-rules/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'wildcardType' => array (
        3 => 'commerce--taxgroup',
    ),
    'contentCallback' => 'commerce::Backend_TaxRuleController::pageForm',
    'contentArguments' => array ('create', NULL, 3),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);
$data['backend/commerce/tax-groups/%/tax-rules/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'commerce--taxgroup',
    ),
    'contentCallback' => 'commerce::Backend_TaxRuleController::pageForm',
    'contentArguments' => array ('edit', NULL, 3),
    'permArguments' => array ('commerce--manage tax entities'),
    'isBackend' => 1,
);

// Task status management
$data['backend/commerce/task-statuses'] = array (
    'module' => 'commerce',
    'heading' => 'Task statuses',
    'description' => 'Manage statuses which can be assigned to orders.',
    'contentCallback' => 'commerce::Backend_TaskStatusController::pageManage',
    'permArguments' => array ('commerce--manage taskstatus entities'),
    'parentId' => 'dashboard/features/commerce',
    'isBackend' => 1,
);
$data['backend/commerce/task-statuses/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'contentCallback' => 'commerce::Backend_TaskStatusController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('commerce--manage taskstatus entities'),
    'isBackend' => 1,
);
$data['backend/commerce/task-statuses/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'contentCallback' => 'commerce::Backend_TaskStatusController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('commerce--manage taskstatus entities'),
    'isBackend' => 1,
);
$data['backend/commerce/task-statuses/%/delete'] = array (
    'module' => 'commerce',
    'heading' => 'Delete',
    'wildcardType' => array (
        3 => 'commerce--taskstatus',
    ),
    'contentCallback' => 'commerce::Backend_TaskStatusController::actionDelete',
    'permArguments' => array ('commerce--manage taskstatus entities'),
    'isBackend' => 1,
);

// Carrier management
$data['backend/commerce/carriers'] = array (
    'module' => 'commerce',
    'heading' => 'Carriers',
    'description' => 'Manage shipping carriers for product delivery.',
    'contentCallback' => 'commerce::Backend_CarrierController::pageManage',
    'permArguments' => array ('commerce--manage carrier entities'),
    'parentId' => 'dashboard/features/commerce',
    'isBackend' => 1,
);
$data['backend/commerce/carriers/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'contentCallback' => 'commerce::Backend_CarrierController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);
$data['backend/commerce/carriers/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'contentCallback' => 'commerce::Backend_CarrierController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);
$data['backend/commerce/carriers/%/scope'] = array (
    'module' => 'commerce',
    'heading' => 'Scope',
    'wildcardType' => array (
        3 => 'commerce--carrier',
    ),
    'contentCallback' => 'commerce::Backend_CarrierController::pageScope',
    'contentArguments' => array (3),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);
$data['backend/commerce/carriers/%/scope/%'] = array (
    'module' => 'commerce',
    'heading' => 'States',
    'wildcardType' => array (
        3 => 'commerce--carrier',
        5 => 'location--country',
    ),
    'contentCallback' => 'commerce::Backend_CarrierController::pageScope',
    'contentArguments' => array (3, 5),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);
$data['backend/commerce/carriers/%/scope/%/%'] = array (
    'module' => 'commerce',
    'heading' => 'Regions',
    'wildcardType' => array (
        3 => 'commerce--carrier',
        5 => 'location--country',
        6 => 'location--state',
    ),
    'contentCallback' => 'commerce::Backend_CarrierController::pageScope',
    'contentArguments' => array (3, 5, 6),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);

$data['backend/commerce/carriers/%/configure'] = array (
    'module' => 'commerce',
    'heading' => 'Configure',
    'wildcardType' => array (
        3 => 'commerce--carrier',
    ),
    'contentCallback' => 'commerce::Backend_CarrierController::pageConfigure',
    'contentArguments' => array (3),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);

$data['backend/commerce/carriers/%/delete'] = array (
    'module' => 'commerce',
    'heading' => 'Delete',
    'wildcardType' => array (
        3 => 'commerce--carrier',
    ),
    'contentCallback' => 'commerce::Backend_CarrierController::actionDelete',
    'contentArguments' => array (3),
    'permArguments' => array ('commerce--manage carrier entities'),
    'isBackend' => 1,
);

// Order management
$data['backend/commerce/orders'] = array (
    'module' => 'commerce',
    'heading' => 'Orders',
    'description' => 'Manage and process orders.',
    'contentCallback' => 'commerce::Backend_OrderController::pageManage',
    'permArguments' => array ('commerce--manage any order entities'),
    'parentId' => 'dashboard/features/commerce',
    'isBackend' => 1,
);

// Duplicate link under "commerce" section
$data['dashboard/user/orders'] = array (
    'module' => 'commerce',
    'heading' => 'Your orders',
    'description' => 'View your order history.',
    'contentCallback' => 'commerce::Frontend_OrderController::pageManage',
    'contentArguments' => array ('self'),
    'permArguments' => array ('commerce--manage own order entities'),
    'ooa' => 20,
    'isBackend' => 1,
);

$data['backend/commerce/orders/%'] = array (
    'module' => 'commerce',
    'headingCallback' => 'system::EntityController::routeHeadingCallback',
    'headingArguments' => array (3),
    'wildcardType' => array (
        3 => 'commerce--order',
    ),
    'contentCallback' => 'commerce::Backend_OrderController::pageView',
    'contentArguments' => array (3),
    'permCallback' => 'system::EntityController::routePermCallback',
    'permArguments' => array (3, 'view'),
    'isBackend' => 1,
);

// Shipments
$data['backend/commerce/orders/%/shipments'] = array (
    'module' => 'commerce',
    'heading' => 'Shipments',
    'wildcardType' => array (
        3 => 'commerce--order',
    ),
    'contentCallback' => 'commerce::Backend_ShipmentController::pageManage',
    'contentArguments' => array (3),
    'permArguments' => array ('commerce--manage shipment entities'),
    'isBackend' => 1,
);
$data['backend/commerce/orders/%/shipments/create'] = array (
    'module' => 'commerce',
    'heading' => 'Create',
    'wildcardType' => array (
        3 => 'commerce--order',
    ),
    'contentCallback' => 'commerce::Backend_ShipmentController::pageForm',
    'contentArguments' => array ('create', NULL, 3),
    'permArguments' => array ('commerce--manage shipment entities'),
    'isBackend' => 1,
);
$data['backend/commerce/orders/%/shipments/%'] = array (
    'module' => 'commerce',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'commerce--order',
        5 => 'commerce--shipment',
    ),
    'contentCallback' => 'commerce::Backend_ShipmentController::pageForm',
    'contentArguments' => array ('create', 5, 3),
    'permArguments' => array ('commerce--manage shipment entities'),
    'isBackend' => 1,
);

// Frontend Cart
$data['cart/shipment-destination'] = array (
    'module' => 'commerce',
    'heading' => 'Shipment destination',
    'contentCallback' => 'commerce::Frontend_CartItemController::pageShipmentDestination',
    'permArguments' => array ('commerce--manage cartitem entities'),
);
$data['cart/items'] = array (
    'module' => 'commerce',
    'heading' => 'Your cart',
    'description' => 'Review items in your cart and place your order.',
    'contentCallback' => 'commerce::Frontend_CartItemController::pageManage',
    'permArguments' => array ('commerce--manage cartitem entities'),
    'parentId' => 'dashboard/user',
    'ooa' => 10,
);
$data['cart/action'] = array (
    'module' => 'commerce',
    'contentCallback' => 'commerce::Frontend_CartItemController::pageAction',
    'permArguments' => array ('commerce--manage cartitem entities'),
);
$data['checkout/account-setup'] = array (
    'module' => 'commerce',
    'heading' => 'Checkout: Setup your account',
    'contentCallback' => 'commerce::Frontend_CheckoutController::pageAccountSetup',
    'permArguments' => array ('commerce--manage cartitem entities'),
);
$data['checkout/address-setup'] = array (
    'module' => 'commerce',
    'heading' => 'Checkout: Setup your address',
    'contentCallback' => 'commerce::Frontend_CheckoutController::pageAddressSetup',
    'permArguments' => array ('commerce--manage own order entities'),
);
$data['checkout/confirm-order'] = array (
    'module' => 'commerce',
    'heading' => 'Checkout: Confirm your order',
    'contentCallback' => 'commerce::Frontend_CheckoutController::pageConfirmOrder',
    'permArguments' => array ('commerce--manage own order entities'),
);