<?php

namespace Module\Media\Classes\Eav;

use Module\Eav\Classes\AttributeObject;
use Module\Eav\Classes\AttrinstObject;
use Natty\Helper\FileHelper;

class Datatype_ImageHelper
extends \Module\Eav\Classes\DatatypeHelperAbstract {
    
    protected static $dtid = 'media--image';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['storage']['location'] = NULL;
        $output['input']['method'] = 'media--upload';
        $output['input']['extensions'] = 'jpg jpeg gif png';
        $output['input']['minReso'] = NULL;
        $output['input']['maxReso'] = '800x800';
        return $output;
    }
    
    protected static function getStorageTableDefinition(AttrinstObject $attrinst) {
        
        $definition = parent::getStorageTableDefinition($attrinst);
        
        unset ($definition['columns']['value']);
        
        $definition['columns'] = array_merge($definition['columns'], array (
            'vid' => array (
                'description' => 'Value ID',
                'type' => 'varchar',
                'length' => 32,
            ),
            'location' => array (
                'type' => 'varchar',
                'length' => 255,
            ),
            'name' => array (
                'description' => 'Name of the file as saved on disk.',
                'type' => 'varchar',
                'length' => 128,
            ),
            'nameWoe' => array (
                'description' => 'File basename without variant indicator and extension.',
                'type' => 'varchar',
                'length' => 255,
            ),
            'extension' => array (
                'description' => 'File extension.',
                'type' => 'varchar',
                'length' => 8,
            ),
            'description' => array (
                'description' => 'A brief description of the file.',
                'type' => 'varchar',
                'length' => 255,
                'default' => NULL,
                'flags' => array ('nullable'),
            ),
            'size' => array (
                'description' => 'Size of the file in bytes.',
                'type' => 'int',
                'length' => 10,
                'flags' => array ('unsigned'),
            ),
            'width' => array (
                'description' => 'Width of the image.',
                'type' => 'int',
                'length' => 10,
                'flags' => array ('unsigned'),
            ),
            'height' => array (
                'description' => 'Height of the image.',
                'type' => 'int',
                'length' => 10,
                'flags' => array ('unsigned'),
            ),
            'tsCreated' => array (
                'description' => 'Timestamp when the file was created.',
                'type' => 'timestamp',
            ),
        ));
        
        $definition['indexes']['vid'] = array (
            'columns' => array ('vid'),
            'unique' => 1,
        );
        
        return $definition;
        
    }
    
    public static function handleSettingsForm(array &$data = array ()) {
        
        parent::handleSettingsForm($data);
        
        $form =& $data['form'];
        $attribute =& $data['attribute'];
        
        switch ( $form->getStatus() ):
            case 'prepare':
                
                if ( isset ($attribute->aiid) ):
                    
                    $location_default = 'eav/attrinst-' . $attribute->aiid . '/' . $attribute->etid;
                    
                    $form->items['storage']['_display'] = 1;
                    $form->items['storage']['_data']['settings.storage.location'] = array (
                        '_widget' => 'input',
                        '_label' => 'Storage Location',
                        '_default' => $location_default,
                        '_description' => 'Uploads would be stored in this directory inside your site storage.',
                        'maxlength' => 128,
                        'required' => 1,
                    );
                    
                endif;
                
                $form->items['input']['_data']['settings.input.extensions'] = array (
                    '_label' => 'Allowed Extensions',
                    '_widget' => 'input',
                    '_description' => 'File extensions separated by space. Example: txt pdf',
                    '_default' => $attribute->settings['input']['extensions'],
                    'maxlength' => 128,
                    'required' => 1,
                );

//                $form->items['input']['_data']['settings.input.minReso'] = array (
//                    '_label' => 'Minimum resolution',
//                    '_widget' => 'input',
//                    '_description' => 'Specify minimum resolution. Example: 600x600.<br />Smaller images would be rejected.',
//                    '_default' => $attribute->settings['input']['minReso'],
//                    'maxlength' => 128,
//                    'class' => array ('widget-small'),
//                );

                $form->items['input']['_data']['settings.input.maxReso'] = array (
                    '_label' => 'Maximum resolution',
                    '_widget' => 'input',
                    '_description' => 'Specify maximum resolution. Example: 600x600.<br />Larger images would be resized to fit.',
                    '_default' => $attribute->settings['input']['maxReso'],
                    'maxlength' => 128,
                    'class' => array ('widget-small'),
                );
                
                break;
            case 'validate':
                
                $form_values = $form->getValues();
                $regex_resolution = '/^[\d]{1,4}x[\d]{1,4}$/';
                
//                $min_reso = $form_values['settings']['input']['minReso'];
//                if ( $min_reso && !preg_match($regex_resolution, $min_reso) ):
//                    $form->items['input']['_data']['settings.input.minReso']['_errors'][] = 'Please enter a valid resolution below 9999x9999.';
//                    $form->isValid(FALSE);
//                endif;
                
                $max_reso = $form_values['settings']['input']['maxReso'];
                if ( $max_reso && !preg_match($regex_resolution, $max_reso) ):
                    $form->items['input']['_data']['settings.input.maxReso']['_errors'][] = 'Please enter a valid resolution below 9999x9999.';
                    $form->isValid(FALSE);
                endif;
                
                break;
        endswitch;
        
    }
    
    public static function saveInstanceValues(AttrinstObject $attrinst, &$entity) {
        
        $acode = $attrinst->acode;
        $connection = \Natty::getDbo();
        $tablename = $attrinst->settings['storage']['tablename'];
        $eid = $entity->getId();
        $ail = 'UNDF';
        if ( $attrinst->isTranslatable && $lang_key = $entity->getHandler()->getKey('language') ):
            $ail = $entity->$lang_key;
        endif;
        
        // Move uploaded files before saving things to database
        $values = ( 1 == $attrinst->settings['input']['nov'] )
                ? array ($entity->$acode) : $entity->$acode;
        
        // Read existing values
        $existing_temp = $connection->read($tablename, array (
            'key' => array (
                'aiid' => $attrinst->aiid,
                'eid' => $entity->getId(),
                'ail' => $ail,
            ),
        ));
        $existing_data = array ();
        foreach ( $existing_temp as $record ):
            $existing_data[$record['vid']] = $record;
        endforeach;
        unset ($existing_temp);
        
        // Delete existing values
        $connection->delete($tablename, array (
            'key' => array (
                'aiid' => $attrinst->aiid,
                'eid' => $entity->getId(),
                'ail' => $ail,
            ),
        ));
        
        // Remove deletions
        foreach ( $values as $vno => $value ):
            
            // Determine value id
            $vid = isset ($value['vid'])
                ? $value['vid'] : FALSE;
            
            // Load details from existing record
            if ( $vid && isset ($existing_data[$vid]) ):
                $value = array_merge($existing_data[$vid], array (
                    'description' => $value['description'],
                    'temporary' => isset ($value['temporary']) ? $value['temporary'] : NULL,
                    'deleted' => isset ($value['deleted']) ? $value['deleted'] : NULL,
                ));
                $values[$vno] = $value;
            endif;
            
            // Process deleted files
            if ( isset ($value['deleted']) && (bool) $value['deleted'] ):
                
                $existing_record = $existing_data[$value['vid']];
                
                FileHelper::unlink($existing_record['location'], array ('variants' => TRUE));
                unset ($values[$vno]);
                
            endif;
            
            // Process temporary files
            if ( isset ($value['temporary']) && (bool) $value['temporary'] ):
                
                // Read image styles
                if ( !isset ($istyle_coll) ):
                    $istyle_coll = \Natty::getHandler('media--imagestyle')->read(array (
                        'key' => array ('status' => 1),
                    ));
                endif;
                
                // Detect year and month of upload
                $upload_y = date('Y');
                $upload_m = date('m');
                
                // Generate upload serial
                $value['serial'] = \Module\System\Classes\SerialHelper::generate('eav--attrinst', array ($attrinst->aiid, $upload_y));
                $value['vid'] = $upload_y . '-' . $value['serial'];
                $value['location'] = 'instance://files/' . $attrinst->settings['storage']['location'] . '/' . $upload_y . '/' . $upload_m;
                
                $upload_dest = FileHelper::instancePath($value['location']);
                
                // Get image dimensions and stuff
                $value['nameWoe'] = pathinfo($value['name'], PATHINFO_FILENAME);
                $upload_info = getimagesize($value['tmp_name']);
                
                // Force maximum allowed dimensions
                if ( $attrinst->settings['input']['maxReso'] ):
                    
                    list ($max_width, $max_height) = explode('x', $attrinst->settings['input']['maxReso']);
                    
                    if ( $upload_info[0] > $max_width || $upload_info[1] > $max_height ):
                        \Natty\Helper\ImageHelper::resize($value['tmp_name'], array (
                            'width' => $max_width,
                            'height' => $max_height,
                            'destination' => $value['tmp_name'],
                        ));
                    endif;
                
                    $upload_info = getimagesize($value['tmp_name']);
                    
                    // Show a message
                    \Natty\Console::message('Some images which exceeded maximum allowed resolution were scaled down.', array (
                        'unique' => TRUE,
                    ));
                    
                endif;
                
                // Additional information about the image
                $value['extension'] = pathinfo($value['name'], PATHINFO_EXTENSION);
                $value['extension'] = strtolower($value['extension']);
                $value['size'] = filesize($value['tmp_name']);
                $value['width'] = $upload_info[0];
                $value['height'] = $upload_info[1];
                
                $conflict = TRUE;
                $upload_suffix = -1;
                while ( $conflict ):
                    
                    $upload_suffix++;
                    
                    $value['name'] = $value['nameWoe'] 
                        . ( $upload_suffix > 0 ? '-' . $upload_suffix : '' )
                        . '-orig'
                        . '.' . $value['extension'];
                    
                    $conflict = file_exists($upload_dest . '/' . $value['name']);
                    
                endwhile;
                
                // Upload filename without variant flag
                if ( $upload_suffix > 0 )
                    $value['nameWoe'] .= '-' . $upload_suffix;
                unset ($upload_suffix);
                
                // Add basename to location
                $value['location'] = $value['location'] . '/' . $value['name'];
                
                // Move upload
                try {
                    
                    FileHelper::moveUpload($value, $value['location']);
                    
                    // Generate variants
                    foreach ( $istyle_coll as $istyle ):
                        $upload_variant_dest = $upload_dest . '/' . $value['nameWoe'] . '-' . $istyle->iscode . '.' . $value['extension'];
                        $istyle->call('createImageStyle', $upload_dest . '/' . $value['name'], $upload_variant_dest);
                    endforeach;
                    
                    unset ($value['temporary']);
                    $value['tsCreated'] = time();
                    $values[$vno] = $value;
                    
                }
                catch (Exception $ex) {
                    unset ($values[$vno]);
                }
                
            endif;
            
        endforeach;
        
        // Reorder values and save to database
        $values = array_values($values);
        foreach ( $values as $vno => $value ):
            
            if ( !is_array($value) )
                continue;
            
            $record = array (
                'egid' => $attrinst->egid,
                'eid' => $eid,
                'aiid' => $attrinst->aiid,
                'ail' => $ail,
                'vno' => $vno,
                'vid' => $value['vid'],
                'location' => $value['location'],
                'name' => $value['name'],
                'nameWoe' => $value['nameWoe'],
                'description' => $value['description'],
                'extension' => $value['extension'],
                'size' => $value['size'],
                'width' => $value['width'],
                'height' => $value['height'],
                'tsCreated' => $value['tsCreated'],
            );
            $connection->insert($tablename, $record);
            
        endforeach;
        
        // Attach the re-indexed values
        $entity->$acode = ( 1 == $attrinst->settings['input']['nov'] )
                ? $value : $values;
        
    }
    
    public static function onBeforeInstanceDelete(AttrinstObject &$attrinst) {
        
        $tablename = self::getUnpluggedTablename($attrinst);
        $file_coll = \Natty::getDbo()->read($tablename, array (
            'conditions' => '1=1'
        ));
        
        foreach ( $file_coll as $key => $file ):
            
            $filename = FileHelper::instancePath($file['location']);
            if ( is_file($filename) ):
                
                // Attempt file deletion
                $status = FileHelper::unlink($filename, array (
                    'variants' => TRUE,
                ));
                
                // Could not delete? Move to the next file.
                if ( !$status )
                    continue;
                
            endif;
            
            unset ($file_coll[$key]);
            
        endforeach;
        
        if ( 0 !== sizeof($file_coll) ) {
            throw new \Exception('Some files for attribute instance ' . $attrinst->aiid . ' "' . $attrinst->name . '" could not be deleted.');
        }
        else {
            $filename = 'instance://files/' . $attrinst->settings['storage']['path'] . '/deleted.txt';
            $filename = FileHelper::instancePath($filename);
            file_put_contents($filename, 'Files for attribute instance ' . $attrinst->aiid . ' were deleted.');
        }
        
        parent::onBeforeInstanceDelete($attrinst);
        
    }
    
}