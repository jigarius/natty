<?php

defined('NATTY') or die;

$data['eav--input'] = array (
    'name' => 'Input Box',
    'datatypes' => array ('eav--varchar', 'eav--integer', 'eav--decimal'),
    'module' => 'eav',
);
$data['eav--textarea'] = array (
    'name' => 'Textarea',
    'datatypes' => array ('eav--text'),
    'module' => 'eav',
);
$data['eav--multiselect'] = array (
    'name' => 'Multi-Select',
    'datatypes' => array ('eav--entityreference'),
    'module' => 'eav',
);