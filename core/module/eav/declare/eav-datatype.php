<?php

defined('NATTY') or die;

$data['eav--varchar'] = array (
    'name' => 'Text - Short',
    'description' => 'Short text upto 255 characters long.',
    'inputMethods' => array ('system--input'),
);
$data['eav--text'] = array (
    'name' => 'Text - Long',
    'description' => 'Long paragraphed text and rich text.',
);
$data['eav--integer'] = array (
    'name' => 'Integer',
    'description' => 'Positive/negative numeric data without decimals upto 20 digits long.',
);
$data['eav--decimal'] = array (
    'name' => 'Decimal',
    'description' => 'Positive numeric data with decimals upto 20 digits long.',
);
$data['eav--boolean'] = array (
    'name' => 'Boolean',
    'description' => 'Yes and no or on and off type data, usually used as flags.',
);
$data['eav--entityreference'] = array (
    'name' => 'Entity reference',
    'description' => 'References to various entities.',
);