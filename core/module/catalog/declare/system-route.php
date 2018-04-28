<?php

defined('NATTY') or die;

$data['dashboard/features/catalog'] = array (
    'module' => 'catalog',
    'heading' => 'Catalog',
    'description' => 'Categories, products and other options for your online catalog.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);
$data['backend/catalog/categories'] = array (
    'module' => 'catalog',
    'heading' => 'Categories',
    'description' => 'Manage categories in your online catalog.',
    'contentCallback' => 'taxonomy::Backend_TermController::pageManage',
    'contentArguments' => array ('catalog-categories'),
    'permArguments' => array ('catalog--manage category entities'),
    'isBackend' => 1,
    'parentId' => 'dashboard/features/catalog',
);

$data['backend/catalog/product-types'] = array (
    'module' => 'catalog',
    'heading' => 'Product Types',
    'description' => 'Manage types of products in your catalog.',
    'contentCallback' => 'catalog::ProductTypeController::pageManage',
    'permArguments' => array ('catalog--manage producttype entities'),
    'isBackend' => 1,
    'parentId' => 'dashboard/features/catalog',
);
$data['backend/catalog/product-types/create'] = array (
    'module' => 'catalog',
    'heading' => 'Create',
    'contentCallback' => 'catalog::ProductTypeController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('catalog--manage producttype entities'),
    'isBackend' => 1,
);
$data['backend/catalog/product-types/%'] = array (
    'module' => 'catalog',
    'heading' => 'Edit',
    'contentCallback' => 'catalog::ProductTypeController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('catalog--manage producttype entities'),
    'isBackend' => 1,
);

$data['backend/catalog/products'] = array (
    'module' => 'catalog',
    'heading' => 'Products',
    'description' => 'Manage products in your online catalog.',
    'contentCallback' => 'catalog::Backend_ProductController::pageManage',
    'permArguments' => array ('catalog--manage product entities'),
    'isBackend' => 1,
    'parentId' => 'dashboard/features/catalog',
);
$data['backend/catalog/products/create'] = array (
    'module' => 'catalog',
    'heading' => 'Create',
    'contentCallback' => 'catalog::Backend_ProductController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('catalog--manage product entities'),
    'isBackend' => 1,
);
$data['backend/catalog/products/%'] = array (
    'module' => 'catalog',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'catalog--product',
    ),
    'contentCallback' => 'catalog::Backend_ProductController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('catalog--manage product entities'),
    'isBackend' => 1,
);

/* Frontend */
$data['catalog'] = array (
    'module' => 'catalog',
    'heading' => 'Catalog',
    'contentCallback' => 'catalog::Frontend_ProductController::pageBrowse',
    'contentArguments' => array (NULL),
    'permArguments' => array ('catalog--access'),
);
$data['catalog/category/%'] = array (
    'module' => 'catalog',
    'wildcardType' => array (
        2 => 'taxonomy--term',
    ),
    'heading' => 'Products',
    'contentCallback' => 'catalog::Frontend_ProductController::pageBrowse',
    'contentArguments' => array (2),
    'permArguments' => array ('catalog--access'),
);
$data['catalog/product/%'] = array (
    'module' => 'catalog',
    'wildcardType' => array (
        2 => 'catalog--product',
    ),
    'headingCallback' => 'system::EntityController::routeHeadingCallback',
    'headingArguments' => array (2),
    'contentCallback' => 'catalog::Frontend_ProductController::pageView',
    'contentArguments' => array (2),
    'permArguments' => array ('catalog--access'),
);