<?php

defined('NATTY') or die;

$data['dashboard/settings/media'] = array (
    'module' => 'media',
    'heading' => 'Media',
    'description' => 'Options related to the file system and media on your site.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permCallback' => 'system::routePermissionCallback',
    'isBackend' => 1,
);

$data['backend/media/image-styles'] = array (
    'module' => 'media',
    'heading' => 'Image Styles',
    'description' => 'Manage various sizes and styles of images used on your site.',
    'contentCallback' => '\\Module\\Media\\Logic\\Backend_ImageStyleController::pageManage',
    'permArguments' => array ('media--administer imagestyle'),
    'parentId' => 'dashboard/settings/media',
    'isBackend' => 1,
);