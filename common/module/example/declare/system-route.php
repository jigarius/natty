<?php

defined('NATTY') or die;

$data['backend/example'] = array (
    'module' => 'example',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'heading' => 'Coding examples',
    'description' => 'Developer documentation and examples to help you start writing code.',
    'permArguments' => array ('example--access examples'),
    'parentId' => 'dashboard/features',
    'isBackend' => 1,
);

// Core examples
$data['backend/example/caching'] = array (
    'module' => 'example',
    'contentCallback' => 'example::DefaultController::pageCaching',
    'heading' => 'Caching',
    'description' => 'Learn how to use database and file system cache APIs.',
    'action' => 'core/caching',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/paging'] = array (
    'module' => 'example',
    'heading' => 'Paging',
    'description' => 'Examples on creating paginated lists of database records.',
    'contentCallback' => 'example::DefaultController::pagePaging',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);

$data['backend/example/email'] = array (
    'module' => 'example',
    'heading' => 'Email examples',
    'description' => 'Examples on using the Email Helper and Email Handler API.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/email/basics'] = array (
    'module' => 'example',
    'heading' => 'Email helper',
    'description' => 'Basic email with manual variable replacement.',
    'contentCallback' => 'example::EmailController::pageBasics',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/email/advanced'] = array (
    'module' => 'example',
    'heading' => 'Email handler',
    'description' => 'Email with automatic variable replacement.',
    'contentCallback' => 'example::EmailController::pageAdvanced',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);

// DBAL Examples
$data['backend/example/dbal'] = array (
    'module' => 'example',
    'heading' => 'Database Abstraction Layer - DBAL',
    'description' => 'Examples related to database operations and utilities.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/dbal/basics'] = array (
    'module' => 'example',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'description' => 'Basic database operations without frills and decorations.',
    'heading' => 'Basics',
    'permArguments' => array ('example--access examples'),
    'ooa' => 10,
    'isBackend' => 1,
);
$data['backend/example/dbal/basics/connecting'] = array (
    'module' => 'example',
    'contentCallback' => 'example::DbalBasicsController::pageConnecting',
    'heading' => 'Connecting',
    'description' => 'Connecting to the database and executing a basic query.',
    'permArguments' => array ('example--access examples'),
    'ooa' => 20,
    'isBackend' => 1,
);
$data['backend/example/dbal/basics/crud'] = array (
    'module' => 'example',
    'contentCallback' => 'example::DbalBasicsController::pageCRUD',
    'heading' => 'Tables',
    'description' => 'Standard methods for creating, reading, updating and deleting records.',
    'permArguments' => array ('example--access examples'),
    'ooa' => 20,
    'isBackend' => 1,
);
$data['backend/example/dbal/basics/serials'] = array (
    'module' => 'example',
    'contentCallback' => 'example::DbalBasicsController::pageSerials',
    'heading' => 'Serials',
    'description' => 'Generating custom serial numbers for custom sequences.',
    'permArguments' => array ('example--access examples'),
    'ooa' => 40,
    'isBackend' => 1,
);
$data['backend/example/dbal/basics/schema'] = array (
    'module' => 'example',
    'contentCallback' => 'example::DbalBasicsController::pageSchema',
    'heading' => 'Serials',
    'description' => 'Creating, altering and dropping tables, columns and indexes.',
    'permArguments' => array ('example--access examples'),
    'ooa' => 40,
    'isBackend' => 1,
);

// Query Builder API

// ORM Examples
$data['backend/example/orm'] = array (
    'module' => 'example',
    'heading' => 'Object Relational Mapping - ORM',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/orm/basic'] = array (
    'module' => 'example',
    'heading' => 'Basic ORM',
    'contentCallback' => 'example::ORMController::pageBasic',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/orm/i18n'] = array (
    'module' => 'example',
    'heading' => 'ORM + I18N',
    'contentCallback' => 'example::ORMController::pageI18n',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/orm/eav'] = array (
    'module' => 'example',
    'heading' => 'EAV ORM',
    'contentCallback' => 'example::ORMController::pageEAV',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);

// Form Examples
$data['backend/example/form'] = array (
    'module' => 'example',
    'heading' => 'Forms & data collection',
    'description' => 'Accepting user data using the Form and related APIs.',
    'contentCallback' => 'system::DashboardController::pageDashboard',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/form/basics'] = array (
    'module' => 'example',
    'heading' => 'Standard Form',
    'description' => 'Accepting basic data-types using basic form widgets.',
    'contentCallback' => 'example::FormController::pageBasics',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/form/uploads'] = array (
    'module' => 'example',
    'heading' => 'Upload Widget',
    'description' => 'Accepting uploads using various upload widgets.',
    'contentCallback' => 'example::FormController::pageUploads',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
$data['backend/example/form/suggestions'] = array (
    'module' => 'example',
    'heading' => 'Suggestion Widgets',
    'description' => 'Input with auto-completion suggestions.',
    'contentCallback' => 'example::ORMController::pageSuggestions',
    'permArguments' => array ('example--access examples'),
    'isBackend' => 1,
);
//$data['backend/example/form/multi-entry'] = array (
//    'module' => 'example',
//    'heading' => 'Multi-entry Widget',
//    'description' => 'Accepting multiple values using the multientry widget.',
//    'contentCallback' => 'example::ORMController::pageMultiEntry',
//    'permArguments' => array ('example--access examples'),
//    'isBackend' => 1,
//);