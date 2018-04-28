<?php

defined('NATTY') or die;

$data['backend/location/countries'] = array (
    'module' => 'location',
    'heading' => 'Countries',
    'description' => 'Manage countries, states and other data.',
    'contentCallback' => 'location::Backend_CountryController::pageManage',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/create'] = array (
    'module' => 'location',
    'heading' => 'Create',
    'contentCallback' => 'location::Backend_CountryController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%'] = array (
    'module' => 'location',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'location--country',
    ),
    'contentCallback' => 'location::Backend_CountryController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

$data['backend/location/countries/%/states'] = array (
    'module' => 'location',
    'heading' => 'States',
    'wildcardType' => array (
        3 => 'location--country',
    ),
    'contentCallback' => 'location::Backend_StateController::pageManage',
    'contentArguments' => array (3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%/states/create'] = array (
    'module' => 'location',
    'heading' => 'Create',
    'wildcardType' => array (
        3 => 'location--country',
    ),
    'contentCallback' => 'location::Backend_StateController::pageForm',
    'contentArguments' => array ('create', NULL, 3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%/states/%'] = array (
    'module' => 'location',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'location--country',
    ),
    'contentCallback' => 'location::Backend_StateController::pageForm',
    'contentArguments' => array ('edit', 5, 3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

$data['backend/location/countries/%/states/%/regions'] = array (
    'module' => 'location',
    'heading' => 'Regions',
    'description' => 'Manage various sub-divisions within a state based on post codes.',
    'wildcardType' => array (
        3 => 'location--country',
        5 => 'location--state',
    ),
    'contentCallback' => 'location::Backend_RegionController::pageManage',
    'contentArguments' => array (5, 3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%/states/%/regions/create'] = array (
    'module' => 'location',
    'heading' => 'Create',
    'wildcardType' => array (
        5 => 'location--state',
    ),
    'contentCallback' => 'location::Backend_RegionController::pageForm',
    'contentArguments' => array ('create', NULL, 5),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%/states/%/regions/%'] = array (
    'module' => 'location',
    'heading' => 'Edit',
    'wildcardType' => array (
        5 => 'location--state',
    ),
    'contentCallback' => 'location::Backend_RegionController::pageForm',
    'contentArguments' => array ('edit', 7, 5),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/location/countries/%/states/%/regions/%/delete'] = array (
    'module' => 'location',
    'heading' => 'Delete',
    'wildcardType' => array (
//        3 => 'location--country',
//        5 => 'location--state',
        7 => 'location--region',
    ),
    'contentCallback' => 'location::Backend_RegionController::actionDelete',
    'contentArguments' => array (3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

// Address management
$data['dashboard/user/addresses'] = array (
    'module' => 'location',
    'heading' => 'Addresses',
    'description' => 'Manage your addresses.',
    'contentCallback' => 'location::Backend_UserAddressController::pageManage',
    'contentArguments' => array ('self'),
    'permArguments' => array ('location--manage own address entities'),
    'isBackend' => 1,
);
$data['backend/people/users/%/addresses'] = array (
    'module' => 'location',
    'heading' => 'Addresses',
    'wildcardType' => array (
        3 => 'people--user',
    ),
    'contentCallback' => 'location::Backend_UserAddressController::pageManage',
    'contentArguments' => array (3),
    'permArguments' => array ('location--manage own address entities', 'location--manage own address entities'),
    'isBackend' => 1,
);
$data['backend/people/users/%/addresses/create'] = array (
    'module' => 'location',
    'heading' => 'Create',
    'wildcardType' => array (
        3 => 'people--user',
    ),
    'contentCallback' => 'location::Backend_UserAddressController::pageForm',
    'contentArguments' => array ('create', NULL, 3),
    'permArguments' => array ('location--manage own address entities', 'location--manage any address entities'),
    'isBackend' => 1,
);
$data['backend/people/users/%/addresses/%'] = array (
    'module' => 'location',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'people--user',
        5 => 'location--useraddress',
    ),
    'contentCallback' => 'location::Backend_UserAddressController::pageForm',
    'contentArguments' => array ('edit', 5, 3),
    'permArguments' => array ('location--manage own address entities', 'location--manage any address entities'),
    'isBackend' => 1,
);
$data['backend/people/users/%/addresses/%/delete'] = array (
    'module' => 'location',
    'heading' => 'Delete',
    'wildcardType' => array (
        5 => 'location--useraddress',
    ),
    'contentCallback' => 'location::Backend_UserAddressController::pageAction',
    'contentArguments' => array (6, 5),
    'permArguments' => array ('location--manage own address entities', 'location--manage any address entities'),
    'isBackend' => 1,
);