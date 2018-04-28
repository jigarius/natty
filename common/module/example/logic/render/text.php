<?php

defined('NATTY') or die;

// Package must be specified for text namespacing
$toptions = array ('package' => $this->pid);

// General text in default language
natty_text('This is text', null, $toptions);

// General text in a custom language
$toptions['language'] = 'es-MX';
natty_text('This is text', null, $toptions);

// Text with variable replacements
natty_text('My name is %name', array ('name' => 'Something'));

/**
 * Grouping texts into bundles for reducing runtime load.
 * By default, all text is considered to belong to a "general" bundle. At 
 * runtime, if a text from the general bundle is requested, the entire
 * bundle is loaded into memory. To avoid this, text can further be grouped
 * into bundles using the "bundle" key in $options.
 */
$toptions['bundle'] = 'form';
natty_text('What is your name?', null, $toptions);

// The form bundle is only loaded into memory on say, a form page.

highlight_file(__FILE__);
natty_debug();