<?php

defined('NATTY') or die;

$output->title = 'Date & Time';
$output->content = natty_render(array (
    array (
        '_render' => 'markup',
        '.markup' => 'Here is an example of basic date & time rendering:',
        '.wrap' => 'p',
        'format' => 'date'
    ),
    array (
        '_render' => 'date',
        '.timestamp' => time(),
        '.wrap' => 'p'
    ),
    array (
        '_render' => 'markup',
        '.markup' => 'Now we show the same thing along with time:',
        '.wrap' => 'p'
    ),
    array (
        '_render' => 'date',
        '.timestamp' => time(),
        '.wrap' => 'p',
        'format' => 'datetime',
    ),
    array (
        '_render' => 'markup',
        '.markup' => 'Now we see only the time in the default timezone:',
        '.wrap' => 'p'
    ),
    array (
        '_render' => 'date',
        '.timestamp' => time(),
        '.wrap' => 'p',
        'format' => 'time',
    ),
    array (
        '_render' => 'markup',
        '.markup' => 'Seeing the current date & time as in New York timezone:',
        '.wrap' => 'p'
    ),
    array (
        '_render' => 'date',
        '.timestamp' => time(),
        '.wrap' => 'p',
        'timezone' => 'America/New_York',
        'language' => 'es_ES',
        'format' => 'datetime',
    )
));