<?php

defined('NATTY') or die;

$data = array (
    '_render' => 'list',
    '.items' => array (
        'Item One',
        'Item Two',
        array (
            // Attributes for the list index
            'attributes' => array (
                'class' => array (
                    'has-children'
                )
            ),
            'data' => array (
                // Contents of the original list item
                'Item Three (Has children)',
                // A nested list declaration within the parent
                array (
                    '_render' => 'list',
                    '.items' => array (
                        'Sub-Item One',
                        'Sub-Item Two'
                    )
                )
            )
        )
    ),
    'attributes' => array (
        'class' => array ('bullet')
    ),
);

$output->title = 'Example: Rendering Lists';
$output->content = natty_render($data);