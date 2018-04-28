<?php

defined('NATTY') or die;

// Menu management
$data['backend/cms/menu'] = array (
    'module' => 'cms',
    'heading' => 'Menus',
    'description' => 'Configure various menus and links that appear under them.',
    'contentCallback' => 'cms::Backend_MenuController::pageManage',
    'permArguments' => array ('cms--manage menu entities'),
    'parentId' => 'dashboard/features',
    'isBackend' => 1
);
$data['backend/cms/menu/create'] = array (
    'module' => 'cms',
    'heading' => 'Create',
    'contentCallback' => 'cms::Backend_MenuController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1,
);
$data['backend/cms/menu/%'] = array (
    'module' => 'cms',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'cms--menu'
    ),
    'contentCallback' => 'cms::Backend_MenuController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1
);
$data['backend/cms/menu/action'] = array (
    'module' => 'cms',
    'contentCallback' => 'cms::Backend_MenuController::pageAction',
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1,
);
$data['backend/cms/menu/%/items'] = array (
    'module' => 'cms',
    'heading' => 'Menu Items',
    'wildcardType' => array (
        3 => 'cms--menu'
    ),
    'contentCallback' => 'cms::Backend_MenuItemController::pageManage',
    'contentArguments' => array (3),
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1,
);
$data['backend/cms/menu/%/items/create'] = array (
    'module' => 'cms',
    'heading' => 'Create',
    'wildcardType' => array (
        3 => 'cms--menu',
    ),
    'contentCallback' => 'cms::Backend_MenuItemController::pageForm',
    'contentArguments' => array ('create', NULL, 3),
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1,
);
$data['backend/cms/menu/%/items/%'] = array (
    'module' => 'cms',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'cms--menu',
        5 => 'cms--menuitem',
    ),
    'contentCallback' => 'cms::Backend_MenuItemController::pageForm',
    'contentArguments' => array ('edit', 5, 3),
    'permArguments' => array ('cms--manage menu entities'),
    'isBackend' => 1,
);

// Read existing content-types
$ctypes = Natty::getHandler('cms--contenttype')->read(array (
    'ordering' => array ('name' => 'asc')
));

// Content type pages
$data['backend/cms/content-types'] = array (
    'module' => 'cms',
    'heading' => 'Content Types',
    'description' => 'Manage types of content that exists on your site. Ex: pages, articles, etc.',
    'contentCallback' => 'cms::Backend_ContentTypeController::pageManage',
    'permArguments' => array ('cms--manage contenttype entities'),
    'parentId' => 'dashboard/settings',
    'isBackend' => 1,
);
$data['backend/cms/content-types/create'] = array (
    'module' => 'cms',
    'heading' => 'Create',
    'contentCallback' => 'cms::Backend_ContentTypeController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('cms--manage contenttype entities'),
    'isBackend' => 1,
);
foreach ( $ctypes as $ctype ):
    $data['backend/cms/content-types/' . $ctype->ctid] = array (
        'module' => 'cms',
        'heading' => 'Edit',
        'contentCallback' => 'cms::Backend_ContentTypeController::pageForm',
        'contentArguments' => array ('edit', 3),
        'wildcardType' => array (
            3 => 'cms--contenttype',
        ),
        'permArguments' => array ('cms--manage contenttype entities'),
        'isBackend' => 1,
    );
endforeach;

// Content pages
$data['backend/cms/content'] = array (
    'module' => 'cms',
    'heading' => 'Content',
    'description' => 'Manage content visible on your wesbite. This includes pages, articles, etc.',
    'contentCallback' => 'cms::Backend_ContentController::pageManage',
    'permArguments' => array ('cms--overview content entities'),
    'parentId' => 'dashboard/features',
    'isBackend' => 1,
);
$data['backend/cms/content/%'] = array (
    'module' => 'cms',
    'contentCallback' => 'cms::Backend_ContentController::pageForm',
    'contentArguments' => array ('edit', 3, NULL),
    'heading' => 'Edit',
    'isBackend' => 1,
);
$data['backend/cms/content/create'] = array (
    'module' => 'cms',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'heading' => 'Create content',
    'permArguments' => array (),
    'isBackend' => 1,
);
foreach ( $ctypes as $ctype ):
    
    // If the user can create any type of content,
    // the create page should be visible
    $data['backend/cms/content/create']['permArguments'][] = 'cms--create ' . $ctype->ctid . ' content entities';
    
    $data['backend/cms/content/create/' . $ctype->ctid] = array (
        'module' => 'cms',
        'heading' => $ctype->name,
        'description' => $ctype->description,
        'wildcardType' => array (
            4 => 'cms--contenttype',
        ),
        'contentCallback' => 'cms::Backend_ContentController::pageForm',
        'contentArguments' => array ('create', NULL, 4),
        'permArguments' => array ('cms--create ' . $ctype->ctid . ' content entities'),
        'isBackend' => 1,
    );

endforeach;
$data['backend/cms/content/action'] = array (
    'module' => 'cms',
    'contentCallback' => 'cms::Backend_ContentController::pageAction',
    'heading' => 'Action',
    'isBackend' => 1,
);

$data['cms/content/%'] = array (
    'module' => 'cms',
    'wildcardType' => array (
        2 => 'cms--content'
    ),
    'headingCallback' => 'system::EntityController::routeHeadingCallback',
    'headingArguments' => array (2),
    'contentCallback' => 'cms::Frontend_ContentController::pageView',
    'contentArguments' => array (2),
    'heading' => 'View content',
    'permCallback' => 'system::EntityController::routePermCallback',
    'permArguments' => array (2, 'view'),
);