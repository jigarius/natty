<?php

defined('NATTY') or die;

$data['standard'] = array (
    'module' => 'commerce',
    'name' => 'Standard',
    'description' => '',
    'configCallback' => '\\Module\\Commerce\\Logic\\Backend_CarrierStandardController::pageConfigure',
);