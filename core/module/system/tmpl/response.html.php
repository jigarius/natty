<?php

defined('NATTY') or die;

// DTD
$attributes['dtd'] = isset ($attributes['dtd']) 
        ? $attributes['dtd'] : "<!DOCTYPE html>\n";
echo $attributes['dtd'];

// BEGIN HTML
echo '<html>';

$rarray['_template'] = 'module/system/tmpl/response.head.html';
echo natty_render_template($rarray);

$rarray['_template'] = 'module/system/tmpl/response.body.html';
echo natty_render_template($rarray);

// END HTML
echo '</html>';