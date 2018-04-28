<?php

defined('NATTY') or die;

$ctypes = \Natty::getHandler('cms--contenttype')->read();

$data['cms--manage contenttype entities'] = array (
    'name' => 'Manage content types',
);
$data['cms--overview content entities'] = array (
    'name' => 'Content overview',
    'description' => 'Whether the user can see a list of all content in the backend.',
);
$data['cms--view published content'] = array (
    'name' => 'View published content',
);
$data['cms--view unpublished content'] = array (
    'name' => 'View unpublished content',
    'description' => 'Allows the user to view unpublished content created by any user.',
    'isCritical' => 1,
);

foreach ( $ctypes as $ctype ):
    $data['cms--create ' . $ctype->ctid . ' content entities'] = array (
        'name' => $ctype->name . ': Create',
    );
    $data['cms--edit own ' . $ctype->ctid . ' content entities'] = array (
        'name' => $ctype->name . ': Edit own',
    );
    $data['cms--edit any ' . $ctype->ctid . ' content entities'] = array (
        'name' => $ctype->name . ': Edit any',
        'isCritical' => 1,
    );
    $data['cms--delete own ' . $ctype->ctid . ' content entities'] = array (
        'name' => $ctype->name . ': Delete own',
    );
    $data['cms--delete any ' . $ctype->ctid . ' content entities'] = array (
        'name' => $ctype->name . ': Delete any',
        'isCritical' => 1,
    );
endforeach;

$data['cms--manage menu entities'] = array (
    'name' => 'Menu: Manage'
);