<?php

namespace Module\Media\Classes\Eav;

use Module\Eav\Classes\AttrinstObject;
use Natty\Helper\FileHelper;
use Module\Eav\Classes\DatatypeHelperAbstract;

abstract class Datatype_FileHelper
extends DatatypeHelperAbstract {
    
    protected static $dtid = 'media--file';
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['storage']['location'] = NULL;
        $output['input']['method'] = 'media--upload';
        $output['input']['extensions'] = '';
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
                'description' => 'Path to the file within storage.',
                'type' => 'varchar',
                'length' => 255,
            ),
            'name' => array (
                'description' => 'Name of the file as saved on disk.',
                'type' => 'varchar',
                'length' => 128,
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
                
                if ( is_a($attribute, '\Module\Eav\Classes\AttrinstObject') ):
                    $form->items['storage']['_display'] = 1;
                    $form->items['storage']['_data']['settings.storage.location'] = array (
                        '_widget' => 'input',
                        '_label' => 'Storage Location',
                        '_default' => $attribute->settings['storage']['location'],
                        '_description' => 'Uploads would be stored in this directory inside your site storage.',
                        'maxlength' => 128,
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
                
                FileHelper::unlink($existing_record['location']);
                unset ($values[$vno]);
                
            endif;
            
            // Process temporary files
            if ( isset ($value['temporary']) && (bool) $value['temporary'] ):
                
                // Detect year and month of upload
                $upload_y = date('Y');
                $upload_m = date('m');
                
                // Generate upload serial
                $value['serial'] = \Module\System\Classes\SerialHelper::generate('eav--attrinst', array ($attrinst->aiid, $upload_y));
                $value['vid'] = $upload_y . '-' . $value['serial'];
                $value['location'] = 'instance://files/' . $attrinst->settings['storage']['location'] . '/' . $upload_y . '/' . $upload_m;
                
                $upload_dest = FileHelper::instancePath($value['location']);
                
                // Add suffix to filename if already exists
                $upload_filename = pathinfo($value['name'], PATHINFO_FILENAME);
                $value['extension'] = pathinfo($value['name'], PATHINFO_EXTENSION);
                $value['extension'] = strtolower($value['extension']);
                $value['size'] = filesize($value['tmp_name']);
                
                $conflict = TRUE;
                $upload_suffix = -1;
                while ( $conflict ):
                    
                    $upload_suffix++;
                    
                    $value['name'] = $upload_filename 
                        . ( $upload_suffix ? '-' . $upload_suffix : '' )
                        . '.' . $value['extension'];
                    
                    $conflict = file_exists($upload_dest . '/' . $value['name']);
                    
                endwhile;
                
                // Add basename to location
                $value['location'] .= '/' . $value['name'];
                
                // Move upload
                try {
                    FileHelper::moveUpload($value, $value['location']);
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
                'description' => $value['description'],
                'extension' => $value['extension'],
                'size' => $value['size'],
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