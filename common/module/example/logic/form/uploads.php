<?php

defined('NATTY') or die;

// Prepare the FileHelper for file operations
use \Natty\Helper\FileHelper as FileHelper;

// Build upload form with proper enctype
$form = new Natty\Form\FormObject(array (
    'id' => 'example/form/standard',
    'enctype' => 'multipart/form-data',
));
$form->items['upload'] = array (
    '_widget' => 'container',
    '_label' => 'File Uploads',
    '_type' => 'fieldset',
    '_data' => array (),
);

// Single file upload
$file_single_path = \Natty::readSetting('system--siteRoot') . '/files/example/upload/file-single.[extension]';
$form->items['upload']['_data']['file_single'] = array (
    '_widget' => 'upload',
    '_label' => 'Single File',
    // Path where the target files would be saved
    '_default' => FileHelper::glob($file_single_path, array ('unique' => 1)),
    // Allowed file extensions
    '_extensions' => array ('txt'),
);

// Multiple file upload
$file_multiple_path = \Natty::readSetting('system--siteRoot') . '/files/example/upload/file-multiple/[filename].[extension]';
$form->items['upload']['_data']['file_multiple'] = array (
    '_widget' => 'upload',
    '_label' => 'Multiple File',
    // Path where the target files would be saved
    '_default' => FileHelper::glob($file_multiple_path),
    // Allowed file extensions
    '_extensions' => array ('txt'),
    // Enable multiple uploads
    'multiple' => 'multiple',
);

$form->actions['submit.save'] = array (
    'type' => 'submit',
    '_label' => 'Submit',
);
$form->actions['cancel'] = array (
    'type' => 'anchor',
    'href' => '#',
    '_label' => 'Cancel'
);

// Call other modules to alter the form
$form->onPrepare();

// Handle submission and check values
if ( $form->isSubmitted() ):
    
    // Custom validation (if any)
    
    // Call other modules to validate the form
    $form->onValidate();

endif;

// If the form is valid, process it
if ( $form->isValid() ):
    
    // Get form values
    $form_values = $form->getValues();
    
    // Process single file upload
    if ( $form_values['file_single'] ):
        
        // Get upload file data
        $upload = $form_values['file_single'];
        
        // Delete existing file
        if ( isset ($upload['deleted']) ):
            FileHelper::unlink($upload['location']);
            \Natty\Console::notice('Single File: File deleted.');
        endif;
        
        // Process new uploads
        if ( isset ($upload['temporary']) ):
            FileHelper::moveUpload($upload, $file_single_path);
            \Natty\Console::notice('Single File: File uploaded.');
        endif;
        
        unset ($upload);
        
    endif;
    
    // Process multiple file upload
    if ( $form_values['file_multiple'] ):
        
        $uploads = $form_values['file_multiple'];
    
        foreach ( $uploads as $upload ):
            
            // Delete existing file
            if ( isset ($upload['deleted']) ):
                FileHelper::unlink($upload['location']);
                \Natty\Console::notice('Multiple File: File <strong>' . $upload['name'] . '</strong> deleted.');
            endif;
            
            // Process new uploads
            if ( isset ($upload['temporary']) ):
                FileHelper::moveUpload($upload, $file_multiple_path, array (
                    'variables' => array (
                        'filename' => pathinfo($upload['name'], PATHINFO_FILENAME)
                    )
                ));
                \Natty\Console::notice('Multiple File: File <strong>' . $upload['name'] . '</strong> uploaded.');
            endif;
            
        endforeach;
        
        unset ($uploads);
        
    endif;

    // Call other modules to process the form
    $form->onProcess();
    
endif;

$output = $form->getRarray();