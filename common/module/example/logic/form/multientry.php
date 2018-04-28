<?php

defined('NATTY') or die;

$form = new Natty\Form\FormObject(array (
    'id' => 'example-form-multientry',
));
$form->items['title'] = array (
    '_widget' => 'multientry',
    '_label' => 'Enter names',
    '_description' => 'This is a multi-entry widget.',
    '_sample' => array (
        '_widget' => 'input',
    ),
    '_default' => array (
        'Foo',
        'Bar',
        'Baz',
    )
);
$form->onPrepare();

// Handle submission
if ( $form->isSubmitted('submit') ):
    
    $form_values = $form->getValues();
    natty_debug($form_values);
    
endif;

// Prepare document
$output = $form->getRarray();