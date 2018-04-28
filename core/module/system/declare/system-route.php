<?php

defined('NATTY') or die;

// Error pages
$data['error/%'] = array (
    'module' => 'system',
    'heading' => 'Error',
    'contentCallback' => 'system::DefaultController::pageError',
);

// Ajax responder
$data['ajax'] = array (
    'module' => 'system',
    'contentCallback' => 'system::DefaultController::pageAjax',
    'isBackend' => 1,
);

// Dashboard
$data['dashboard'] = array (
    'module' => 'system',
    'heading' => 'Dashboard',
    'description' => 'Want to do something? This is the place to get started.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);
$data['dashboard/features'] = array (
    'heading' => 'Features',
    'description' => 'Primary features and objects that exist on your website.',
    'module' => 'system',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'ooa' => 100,
    'isBackend' => 1,
);
$data['dashboard/settings'] = array (
    'heading' => 'Settings',
    'description' => 'Contains all settings and advanced options related to your site.',
    'module' => 'system',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'ooa' => 970,
    'isBackend' => 1,
);
$data['dashboard/settings/appearance'] = array (
    'heading' => 'Appearance',
    'description' => 'Settings related to the site\'s appearance.',
    'module' => 'system',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);
$data['dashboard/settings/technical'] = array (
    'heading' => 'Technical',
    'description' => 'Technical settings for developer and technical staff.',
    'module' => 'system',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);
$data['dashboard/report'] = array (
    'heading' => 'Reports',
    'description' => 'Contains all reports related to your site.',
    'module' => 'system',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'ooa' => 980,
    'isBackend' => 1,
);

// Module management
$data['backend/system/module'] = array (
    'module' => 'system',
    'heading' => 'Modules',
    'description' => 'Install, uninstall and configure modules installed on your site.',
    'contentCallback' => 'system::Backend_PackageController::pageManage',
    'contentArguments' => array (2),
    'permArguments' => array ('system--administer'),
    'parentId' => 'dashboard/settings',
    'isBackend' => 1
);
$data['backend/system/module/install'] = array (
    'module' => 'system',
    'heading' => 'Install module',
    'contentCallback' => 'system::Backend_PackageController::pageInstall',
    'contentArguments' => array ('module'),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1
);
$data['backend/system/package/action'] = array (
    'module' => 'system',
    'contentCallback' => 'system::Backend_PackageController::pageAction',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

// Skin management
$data['backend/system/skin'] = array (
    'module' => 'system',
    'heading' => 'Skins',
    'description' => 'Install, uninstall and configure skins installed on your site.',
    'contentCallback' => 'system::Backend_PackageController::pageManage',
    'contentArguments' => array (2),
    'permArguments' => array ('system--administer'),
    'parentId' => 'dashboard/settings/appearance',
    'isBackend' => 1,
);
$data['backend/system/skin/install'] = array (
    'module' => 'system',
    'heading' => 'Install skin',
    'contentCallback' => 'system::Backend_PackageController::pageInstall',
    'contentArguments' => array ('skin'),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1
);

$skin_fe = \Natty::readSetting('system--frontendSkin', 'default');
$skin_be = \Natty::readSetting('system--backendSkin', 'default');

// Read all active skins
$skin_coll = \Natty::getHandler('system--package')->read(array (
    'key' => array ('type' => 'skin', 'status' => 1),
));

// Block management
$data['backend/system/block-inst'] = array (
    'module' => 'system',
    'heading' => 'Blocks',
    'description' => 'Manage placement of various blocks on your site.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--manage blockinst entities'),
    'parentId' => 'dashboard/settings/appearance',
    'isBackend' => 1,
);

foreach ($skin_coll as $skin):
    
    $data['backend/system/block-inst/' . $skin->code] = array (
        'module' => 'system',
        'heading' => 'Blocks: ' . $skin->name,
        'description' => 'Manage placement of blocks for the skin "' . $skin->name . '".',
        'contentCallback' => 'system::Backend_BlockInstController::pageManage',
        'contentArguments' => array (3),
        'permArguments' => array ('system--manage blockinst entities'),
        'isBackend' => 1,
    );

    $data['backend/system/block-inst/' . $skin->code . '/create'] = array (
        'module' => 'system',
        'heading' => 'Create',
        'contentCallback' => 'system::Backend_BlockInstController::pageCreate',
        'contentArguments' => array (3),
        'permArguments' => array ('system--manage blockinst entities'),
        'isBackend' => 1,
    );
    $data['backend/system/block-inst/' . $skin->code . '/create/%'] = array (
        'module' => 'system',
        'heading' => 'Create',
        'contentCallback' => 'system::Backend_BlockInstController::pageForm',
        'contentArguments' => array ('create', NULL, 3, 5),
        'permArguments' => array ('system--manage blockinst'),
        'isBackend' => 1,
    );
    $data['backend/system/block-inst/' . $skin->code . '/%'] = array (
        'module' => 'system',
        'heading' => 'Edit',
        'contentCallback' => 'system::Backend_BlockInstController::pageForm',
        'contentArguments' => array ('edit', 4, 3, NULL),
        'permArguments' => array ('system--manage blockinst entities'),
        'wildcardType' => array (
            4 => 'system--blockinst'
        ),
        'isBackend' => 1,
    );
    
endforeach;

$data['backend/system/block-inst/action'] = array (
    'module' => 'system',
    'contentCallback' => 'system::Backend_BlockInstController::pageAction',
    'permArguments' => array ('system--manage blockinst entities'),
    'isBackend' => 1,
);

// Settings
$data['dashboard/settings/site-info'] = array (
    'module' => 'system',
    'heading' => 'Site Info',
    'description' => 'Settings related to the website/application.',
    'contentCallback' => 'system::Backend_SettingsController::pageSiteInfoForm',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['dashboard/settings/technical/cron'] = array (
    'module' => 'system',
    'heading' => 'Cron',
    'description' => 'Settings related to cron / background maintenance tasks.',
    'contentCallback' => 'system::Backend_SettingsController::pageCronForm',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['dashboard/settings/technical/offline-mode'] = array (
    'module' => 'system',
    'heading' => 'Offline Mode',
    'description' => 'Put the site offline for mentainance tasks.',
    'contentCallback' => 'system::Backend_SettingsController::pageOfflineModeForm',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['dashboard/settings/technical/error-display'] = array (
    'module' => 'system',
    'heading' => 'Error display',
    'description' => 'Handle how errors and exceptions are handled.',
    'contentCallback' => 'system::Backend_SettingsController::pageErrorDisplayForm',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['dashboard/settings/locale'] = array (
    'module' => 'system',
    'heading' => 'Locale',
    'description' => 'Settings related to internationalization and localization.',
    'contentCallback' => 'system::Backend_SettingsController::pageLocaleSettings',
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

// Email management
$data['backend/system/emails'] = array (
    'heading' => 'Email formats',
    'description' => 'Manage formatting of out outgoing emails.',
    'contentCallback' => 'system::Backend_EmailController::pageManage',
    'permArguments' => array ('system--manage email entities'),
    'parentId' => 'dashboard/settings/appearance',
    'isBackend' => 1,
);

// URL Aliases
$data['backend/system/rewrites'] = array (
    'heading' => 'URL Rewrites',
    'description' => 'System URLs and their user-defined re-writes.',
    'contentCallback' => 'system::Backend_RewriteController::pageManage',
    'permArguments' => array ('system--manage rewrite entities'),
    'parentId' => 'dashboard/settings/technical',
    'isBackend' => 1,
);
$data['backend/system/rewrites/create'] = array (
    'heading' => 'Create',
    'contentCallback' => 'system::Backend_RewriteController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('system--manage rewrite entities'),
    'isBackend' => 1,
);
$data['backend/system/rewrites/%'] = array (
    'heading' => 'Edit',
    'contentCallback' => 'system::Backend_RewriteController::pageForm',
    'contentArguments' => array ('create', 3),
    'permArguments' => array ('system--manage rewrite entities'),
    'isBackend' => 1,
);
$data['backend/system/rewrites/action'] = array (
    'heading' => 'Action',
    'contentCallback' => 'system::Backend_RewriteController::pageAction',
    'permArguments' => array ('system--manage rewrite entities'),
    'isBackend' => 1,
);

// Localization
$data['backend/system/languages'] = array (
    'module' => 'system',
    'heading' => 'Languages',
    'description' => 'Manage languages available on your site.',
    'contentCallback' => 'system::LanguageController::pageManage',
    'permArguments' => array ('system--administer'),
//    'parentId' => 'dashboard/settings/locale',
    'isBackend' => 1,
);

$data['backend/system/currencies'] = array (
    'module' => 'system',
    'heading' => 'Currencies',
    'description' => 'Manage currencies available on your site.',
    'contentCallback' => 'system::Backend_CurrencyController::pageManage',
    'permArguments' => array ('system--administer'),
//    'parentId' => 'dashboard/settings/locale',
    'isBackend' => 1,
);
$data['backend/system/currencies/create'] = array (
    'module' => 'system',
    'heading' => 'Create',
    'contentCallback' => 'system::Backend_CurrencyController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);
$data['backend/system/currencies/%'] = array (
    'module' => 'system',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'system--currency',
    ),
    'contentCallback' => 'system::Backend_CurrencyController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('system--administer'),
    'isBackend' => 1,
);

// Reports
$data['backend/system/incidents'] = array (
    'module' => 'system',
    'heading' => 'Incident Log',
    'description' => 'A history of incidents which took place on the site.',
    'contentCallback' => 'system::Backend_IncidentController::pageManage',
    'permArguments' => array ('system--administer'),
    'parentId' => 'dashboard/report',
    'isBackend' => 1,
);