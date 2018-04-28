<?php

namespace Module\System\Classes\System;

use Natty\Helper\FileHelper as FileHelper;

class FormWidget_UploadHelper
extends \Natty\Form\WidgetHelperAbstract {
    
    public static function prepare(array &$definition) {
        
        $definition = array_merge(array (
            'type' => 'file',
            '_extensions' => array (),
            '_default' => FALSE,
            '_extras' => array (),
        ), $definition);
        
        // Extra fields to be rendered for each value
        $definition['_extras']['name'] = array (
            '_render' => 'form_item',
            '_widget' => 'input',
            'type' => 'hidden',
        );
        $definition['_extras']['size'] = array (
            '_render' => 'form_item',
            '_widget' => 'input',
            'type' => 'hidden',
        );
        
        // Convert default values to file arrays
        if ( $definition['_default'] ):
            if ( isset ($definition['multiple']) ) {
                foreach ( $definition['_default'] as &$item ):
                    if ( is_string($item) )
                        $item = FileHelper::info($item);
                    unset ($item);
                endforeach;
            }
            else {
                if ( is_string($definition['_default']) )
                    $definition['_default'] = FileHelper::info($definition['_default']);
            }
        endif;
        
        parent::prepare($definition);
        
    }
    
    public static function setValue(array &$definition) {
        
        if ( !$definition['name'] )
            return;
        if ( $definition['readonly'] )
            return;
        
        // Extra fields
        $other_input_defaults = array ();
        foreach ( $definition['_extras'] as $extra_name => &$extra_definition ):
            if ( !isset ($extra_definition['_default']) )
                $extra_definition['_default'] = NULL;
            $other_input_defaults[$extra_name] = $extra_definition['_default'];
            unset ($extra_definition);
        endforeach;
        
        // Determine default value
        $definition['_value'] = $definition['_default'];
        if ( isset ($definition['multiple']) && !$definition['_value'] )
            $definition['_value'] = array ();
        
        // Determine deleted files (applies only when we have a default value)
        if ( $definition['_default'] && $post_data = natty_array_get($_POST, $definition['name']) ):
            if ( isset ($definition['multiple']) ) {
                foreach ( $post_data as $vno => $upload ):
                    
                    // Merge post data with defaults
                    $upload = array_merge($other_input_defaults, $upload);
                    
                    // Unset system keys
                    unset ($upload['location'], $upload['name'], $upload['extension'], $upload['size']);
                    
                    // Merge post data with value
                    $definition['_value'][$vno] = array_merge($definition['_value'][$vno], $upload);
                    if ( isset ($upload['deleted']) )
                        $definition['_value'][$vno]['deleted'] = 1;
                    
                endforeach;
            }
            else {
                
                $upload = $post_data;
                
                // Merge post data with defaults
                $upload = array_merge($other_input_defaults, $post_data);
                
                // Unset system keys
                unset ($upload['location'], $upload['name'], $upload['extension'], $upload['size']);
                
                // Merge post data with value
                $definition['_value'] = array_merge($definition['_value'], $upload);
                if ( isset ($upload['deleted']) )
                    $definition['_value']['deleted'] = 1;
                
            }
        endif;
        
        // Merge upload data with existing data
        $upload_data = \Natty\Helper\FileHelper::readUpload($definition['name'], isset ($definition['multiple']));
        
        if ( $upload_data ):
            if ( isset ($definition['multiple']) ) {
                foreach ( $upload_data as $upload ):
                    $upload['temporary'] = 1;
                    $upload = array_merge($other_input_defaults, $upload);
                    array_push($definition['_value'], $upload);
                endforeach;
            }
            else {
                $upload = $upload_data;
                $upload['temporary'] = 1;
                $upload = array_merge($other_input_defaults, $upload);
                $definition['_value'] = $upload;
            }
        endif;
        
    }
    
    public static function render( array $definition ) {
        
        // Pre-render
        self::preRender($definition);
        
        // Add type class to container
        $definition['_container'][] = 'input-' . $definition['type'];
        
        // Add message for allowed extensions
        if ( $definition['_extensions'] ):
            if ( $definition['_description'] )
                $definition['_description'] .= '<br />';
            $definition['_description'] .= 'Allowed file types: ' . implode(', ', $definition['_extensions']);
        endif;
        
        // Render label and messages
        $prefix_markup = self::renderPrefix($definition);
        $suffix_markup = self::renderSuffix($definition);
        
        $widget_markup = '<input' . natty_render_attributes($definition) . '/>';
        
        // Generate markup for existing files
        $existing_files_markup = '';
        if ( $definition['_value'] ):
            
            $existing_files = $definition['_value'];
            if ( !isset ($definition['multiple']) )
                $existing_files = array ($definition['_value']);
            
            // Preview renderer
            $preview_callback = isset ($definition['_valuePreviewCallback'])
                ? $definition['_valuePreviewCallback'] : array (__CLASS__, 'renderValuePreview');
            
            foreach ( $existing_files as $vno => $existing_file ):
                
                // Ignore temporary files
                if ( isset ($existing_file['temporary']) )
                    continue;
                
                // Determine preview href for the file
                if ( !isset ($existing_file['_previewHref']) ):
                    
                    $existing_file['_previewHref'] = FALSE;
                    
                    $_preview_href = $existing_file['location'];
                    if ( 0 === strpos($_preview_href, 'instance://') )
                        $_preview_href = FileHelper::instancePath($_preview_href, 'root');
                    
                    $existing_file['_previewHref'] = str_replace(NATTY_ROOT . '/', NATTY_BASE, $_preview_href);
                    
                    unset ($_preview_href);
                    
                endif;
                
                // Render preview
                $existing_file['_preview'] = call_user_func($preview_callback, $existing_file);
                
                // Determine file label
                $existing_file['_previewLabel'] = $existing_file['name'];
                if ( $existing_file['_previewLabel'] )
                    $existing_file['_previewLabel'] = '<a href="' . $existing_file['_previewHref'] . '" target="_blank">' . $existing_file['name'] . '</a>';
                
                // Generate other custom fields (if any)
                foreach ( $definition['_extras'] as $extra_name => $extra_definition ):
                    
                    $extra_definition['_render'] = 'form_item';
                    $extra_definition['name'] = isset ($definition['multiple'])
                        ? $definition['name'] . '.' . $vno . '.' . $extra_name
                        : $definition['name'] . '.' . $extra_name;
                
                    // Assign default value
                    if ( isset ($existing_file[$extra_name]) )
                        $extra_definition['_value'] = $existing_file[$extra_name];
                
                    $extra_name = $extra_definition['name'];
                    $other_inputs[$extra_name] = $extra_definition;
                    
                    unset ($extra_name, $extra_definition);
                    
                endforeach;

                // Deleted checkbox (hidden)
                $deleted_checkbox = array (
                    'type' => 'checkbox',
                    'name' => isset ($definition['multiple'])
                        ? $definition['name'] . '.' . $vno . '.deleted' : $definition['name'] . '.deleted',
                    'value' => 1,
                    'title' => 'Check to delete this file.',
                    'class' => array ('cb-deleted'),
                );
                
                // See if the file is marked for deletion
                if ( isset ($existing_file['deleted']) )
                    $deleted_checkbox['checked'] = 'checked';

                // Render file entry
                $existing_files_markup .= '<div class="item' . (isset ($existing_file['deleted']) ? ' deleted' : '') . '" data-ui-init="system-existing-form-value">'
                            . $existing_file['_preview']
                            . '<div class="info-cont">'
                                . '<div class="prop-filename">'
                                    . '<input ' . natty_render_attributes($deleted_checkbox) . ' /> '
                                    . $existing_file['_previewLabel']
                                    . ' <button type="button" class="k-button button-delete">Delete</button>'
                                . '</div>'
                                . natty_render($other_inputs)
                            . '</div>'
                        . '</div>';
                
            endforeach;
            
            $existing_files_markup = '<div class="existing-values">' . $existing_files_markup . '</div>';
            
        endif;
        
        return '<div class="' . implode(' ', $definition['_container']) . '">'
                . $prefix_markup . $widget_markup . $suffix_markup
                . $existing_files_markup
            . '</div>';
        
    }
    
    public static function validate(&$definition, $form) {
        
        $output = TRUE;
        
        // No values exist? Nothing to validate!
        if ( !$definition['_value'] )
            return TRUE;
        
        // Determine values to check
        $item_values = isset ($definition['multiple'])
                ? $definition['_value'] : array ($definition['_value']);
        
        foreach ( $item_values as $item_value ):
            
            // Validate extensions
            if ( $definition['_extensions'] ):
                $item_extension = pathinfo($item_value['name'], PATHINFO_EXTENSION);
                $item_extension = strtolower($item_extension);
                if ( !in_array($item_extension, $definition['_extensions']) ):
                    $definition['_errors']['extension'] = 'File of type "' . $item_extension . '" is not supported. Please upload some another file.';
                    $output = FALSE;
                endif;
            endif;
            
        endforeach;
        
        return $output;
        
    }
    
    public static function renderValuePreview(array $value) {

        $output = '';
        
        // Determine file type
        $value_type = FALSE;
        switch ( $value['extension'] ):
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $value_type = 'image';
                break;
            default:
                $value_type = $value['extension'];
                break;
        endswitch;

        switch ( $value_type ):
            case 'image':
                $value['_previewMarkup'] = '<a href="' . $value['_previewHref'] . '" target="_blank"><img src="' . $value['_previewHref'] . '" alt="" /></a>';
                break;
        endswitch;

        // Wrap preview in container (if any)
        if ( isset ($value['_previewMarkup']) )
            $output = '<div class="preview-cont type-image">' . $value['_previewMarkup'] . '</div>';
        
        return $output;
        
    }
    
}