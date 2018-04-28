<?php

defined('NATTY') or die;

/**
 * Demonstrates rendering of numbers and money
 */

$output->title = 'Example: Numbers & Currency';
$output->content = array (
    array (
        '_render' => 'markup',
        '.markup' => 'Rendering of standard numbers with and without decimals',
        '.wrap' => 'p'
    ),
    array (
        '_render' => 'number',
        '.number' => '200000',
        'decimals' => 2
    )
);