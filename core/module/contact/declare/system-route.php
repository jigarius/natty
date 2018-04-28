<?php

defined('NATTY') or die;

$data['backend/contact/categories'] = array (
    'module' => 'contact',
    'heading' => 'Contact Categories',
    'description' => 'Categories for messages sent through the site-wide contact form.',
    'contentCallback' => 'contact::Backend_CategoryController::pageManage',
    'contentArguments' => array (),
    'parentId' => 'dashboard/settings',
    'permArguments' => array ('contact--administer'),
);
$data['backend/contact/categories/%'] = array (
    'module' => 'contact',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'contact--category',
    ),
    'contentCallback' => 'contact::Backend_CategoryController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('contact--administer'),
);
$data['backend/contact/categories/create'] = array (
    'module' => 'contact',
    'heading' => 'Create',
    'contentCallback' => 'contact::Backend_CategoryController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('contact--administer'),
);
$data['backend/contact/categories/action'] = array (
    'module' => 'contact',
    'contentCallback' => 'contact::Backend_CategoryController::pageAction',
    'permArguments' => array ('contact--administer'),
);

$data['contact'] = array (
    'module' => 'contact',
    'heading' => 'Contact',
    'contentCallback' => 'contact::Frontend_DefaultController::pageForm',
    'contentArguments' => array (NULL),
    'permArguments' => array ('contact--contact site'),
);