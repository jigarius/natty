<?php

defined('NATTY') or die;

$data['dashboard/user'] = array (
    'module' => 'people',
    'heading' => 'Your account',
    'description' => 'Common tasks and options related to your account.',
    'ooa' => 999,
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'parentId' => 'dashboard',
    'isBackend' => 1,
);
$data['dashboard/settings/people'] = array (
    'module' => 'people',
    'heading' => 'People',
    'description' => 'User account, roles and permission related settings.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('system--view dashboard'),
    'isBackend' => 1,
);

$data['sign-in'] = array (
    'module' => 'people',
    'heading' => 'Sign in',
    'contentCallback' => 'people::Frontend_UserController::pageSignIn',
);
$data['dashboard/user/edit-profile'] = array (
    'module' => 'people',
    'heading' => 'Edit profile',
    'description' => 'Update your account information and password.',
    'contentCallback' => 'people::Backend_UserController::pageForm',
    'contentArguments' => array ('edit', 'self'),
    'permArguments' => array ('people--edit own account'),
    'parentId' => 'dashboard/user',
    'ooa' => 980,
    'isBackend' => 1,
);
$data['user/forgot-password'] = array (
    'module' => 'people',
    'heading' => 'Forgot password',
    'contentCallback' => 'people::Frontend_RecoveryController::pageForgotPassword',
    'contentArguments' => array (),
    'permArguments' => array (),
);
$data['sign-out'] = array (
    'module' => 'people',
    'heading' => 'Sign out',
    'description' => 'End your current session on this site.',
    'contentCallback' => 'people::Frontend_UserController::pageSignOut',
    'parentId' => 'dashboard/user',
    'ooa' => 990,
);
$data['sign-up'] = array (
    'module' => 'people',
    'heading' => 'Sign up',
    'description' => 'Create an account on this site.',
    'contentCallback' => 'people::Frontend_UserController::pageSignUp',
    'permCallback' => 'people::DefaultController::permSignUp',
    'permArguments' => array (),
);
$data['backend/people/user'] = array (
    'module' => 'people',
    'heading' => 'User accounts',
    'description' => 'Edit and create user accounts on the site.',
    'contentCallback' => 'people::Backend_UserController::pageManage',
    'permArguments' => array ('people--manage user entities'),
    'parentId' => 'dashboard/features',
    'isBackend' => 1,
);
$data['backend/people/user/%'] = array (
    'module' => 'people',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'people--user'
    ),
    'contentCallback' => 'people::Backend_UserController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('people--manage user entities'),
    'isBackend' => 1,
);
$data['backend/people/user/create'] = array (
    'module' => 'people',
    'heading' => 'Create',
    'contentCallback' => 'people::Backend_UserController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('people--manage user entities'),
    'isBackend' => 1,
);
$data['backend/people/user/action'] = array (
    'module' => 'people',
    'contentCallback' => 'people::Backend_UserController::pageActions',
    'contentArguments' => array (),
    'isBackend' => 1,
);

$data['backend/people/roles'] = array (
    'module' => 'people',
    'heading' => 'User Roles',
    'description' => 'Manage user roles and their permissions.',
    'contentCallback' => 'people::Backend_RoleController::pageManage',
    'permArguments' => array ('people--manage role entities'),
    'parentId' => 'dashboard/settings/people',
    'isBackend' => 1,
);
$data['backend/people/roles/%'] = array (
    'module' => 'people',
    'heading' => 'Edit',
    'wildcardType' => array (
        3 => 'people--role'
    ),
    'contentCallback' => 'people::Backend_RoleController::pageForm',
    'contentArguments' => array ('edit', 3),
    'permArguments' => array ('people--manage role entities'),
    'isBackend' => 1,
);
$data['backend/people/roles/create'] = array (
    'module' => 'people',
    'heading' => 'Create',
    'contentCallback' => 'people::Backend_RoleController::pageForm',
    'contentArguments' => array ('create', NULL),
    'permArguments' => array ('people--manage role entities'),
    'isBackend' => 1
);
$data['backend/people/roles/%/permissions'] = array (
    'module' => 'people',
    'heading' => 'Permissions',
    'wildcardType' => array (
        3 => 'people--role'
    ),
    'contentCallback' => 'people::Backend_RoleController::pagePermissions',
    'contentArguments' => array (3),
    'permArguments' => array ('people--manage permissions'),
    'isBackend' => 1,
);
$data['backend/people/roles/action'] = array (
    'module' => 'people',
    'contentCallback' => 'people::Backend_RoleController::pageActions',
    'contentArguments' => array (),
    'isBackend' => 1,
);
$data['backend/people/permissions'] = array (
    'module' => 'people',
    'heading' => 'Permissions',
    'description' => 'Manage and review permissions user permissions.',
    'contentCallback' => 'people::Backend_RoleController::pagePermissions',
    'contentArguments' => array (NULL),
    'permArguments' => array ('people--manage permissions'),
    'parentId' => 'dashboard/settings/people',
    'isBackend' => 1,
);
$data['backend/people/configure'] = array (
    'module' => 'people',
    'heading' => 'General',
    'contentCallback' => 'people::Backend_SettingsController::pageDefault',
    'contentArguments' => array (NULL),
    'permArguments' => array ('system--administer'),
    'parentId' => 'dashboard/settings/people',
    'isBackend' => 1,
);

// Token consumption URLs
$data['user/ota/%'] = array (
    'module' => 'people',
    'heading' => 'One time access',
    'wildcardType' => array (
        2 => 'people--token',
    ),
    'contentCallback' => 'people::Frontend_RecoveryController::pageOneTimeAccess',
    'contentArguments' => array (2),
    'permArguments' => array (),
);
$data['user/vem/%'] = array (
    'module' => 'people',
    'wildcardType' => array (
        2 => 'people--token',
    ),
    'contentCallback' => 'people::Frontend_UserController::pageValidateEmail',
    'contentArguments' => array (2),
    'permArguments' => array (),
);