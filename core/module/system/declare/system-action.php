<?php

defined('NATTY') or die;

$data['system--administer'] = array (
    'name' => 'Administer the system',
    'description' => 'Change core site settings like site information, modules, skins, etc.',
    'isCritical' => 1,
);
$data['system--view dashboard'] = array (
    'name' => 'View dashboard',
);
$data['system--manage rewrite entities'] = array (
    'name' => 'Manage URL rewrites',
);
$data['system--manage blockinst entities'] = array (
    'name' => 'Manage block instances',
    'description' => 'Manage various content blocks appearing on the site.',
);