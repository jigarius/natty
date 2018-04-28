<?php

defined('NATTY') or die;

// Prepare form
$form = new Natty\Form\FormObject(array (
    'id' => 'example-form-autocomplete',
));
$form->items['text'] = array (
    '_label' => 'Basic Autocomplete',
    '_widget' => 'input',
    '_description' => 'Shows suggestions to help complete text input.',
    'data-ui-init' => array ('autocomplete'),
    'data-autocomplete-source' => \Natty::url('backend/example/school/read'),
);
$form->items['singleValue'] = array (
    '_label' => 'Single Reference',
    '_widget' => 'dropdown',
    '_description' => 'Shows suggestions to help complete text input and posts an ID in form data.',
    'data-ui-init' => array ('multiselect'),
    'data-multiselect-source' => \Natty::url('backend/example/school/read'),
    'data-multiselect-data-value-field' => 'sid',
);
$form->items['multiValue'] = array (
    '_label' => 'Multiple References',
    '_widget' => 'dropdown',
    '_description' => 'Shows suggestions to help complete text input and posts multiple IDs in form data.',
    'multiple' => 1,
    'data-ui-init' => array ('multiselect'),
    'data-multiselect-source' => \Natty::url('backend/example/school/read'),
    'data-multiselect-data-value-field' => 'sid',
);

$form->onPrepare();

// Validate form
if ( $form->isSubmitted() ):
    
    $form->onValidate();
    
endif;

// Process form
if ( $form->isValid() ):
    
    $form_values = $form->getValues();
    \Natty\Console::debug($_POST);
    
endif;

$output = $form->getRarray();