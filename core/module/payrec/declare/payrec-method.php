<?php

defined('NATTY') or die;

$data['receipt-check'] = array (
    'module' => 'payrec',
    'type' => 'receipt',
    'name' => 'Check or draft',
    'description' => 'Payment by checks or demand drafts.',
);
$data['receipt-wire'] = array (
    'module' => 'payrec',
    'type' => 'receipt',
    'name' => 'Wire transfer',
    'description' => 'Payment by direct bank wire transfer.',
);