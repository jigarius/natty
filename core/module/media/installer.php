<?php

namespace Module\Media;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__media_imagestyle',
            'description' => 'Image style data.',
            'columns' => array (
                'isid' => array (
                    'description' => 'Image Style ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'iscode' => array (
                    'description' => 'Image Style Code.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'status' => array (
                    'description' => 'Whether the style is deleted or not.',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('isid'),
                ),
                'iscode' => array (
                    'columns' => array ('iscode'),
                    'unique' => 1,
                )
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__media_imagestyle_i18n',
            'description' => 'Image style i18n data.',
            'columns' => array (
                'isid' => array (
                    'description' => 'Image Style ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('isid', 'ail'),
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__media_imagestyle_proc',
            'description' => 'Image style processing options.',
            'columns' => array (
                'isid' => array (
                    'description' => 'Image Style ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'ipid' => array (
                    'description' => 'Image Processor ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'ooa' => array (
                    'description' => 'Order of appearance',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'settings' => array (
                    'description' => 'Serialized settings.',
                    'type' => 'blob',
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('isid', 'ipid'),
                ),
            )
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->dropTable('%__media_imagestyle');
        $schema_helper->dropTable('%__media_imagestyle_i18n');
        $schema_helper->dropTable('%__media_imagestyle_proc');
        
    }
    
    public static function enable() {
        
        // First run
        if ( \Natty::readSetting('media--installing') ):

            $dbo = \Natty::getDbo();
            $istyle_handler = \Natty::getHandler('media--imagestyle');

            // Create style "thumb"
            $istyle = $istyle_handler->createAndSave(array (
                'iscode' => 'thumb',
                'name' => 'Thumbnail - 200x200',
                'isLocked' => 1,
            ));

            // Add "resize" effect to "thumb"
            $record = array (
                'isid' => $istyle->isid,
                'ipid' => 'media--resize',
                'settings' => array (
                    'resolution' => '400x400',
                ),
            );
            $istyle_handler::writeImageProcSettings($record);

            // Create attributes
            $attribute_handler = \Natty::getHandler('eav--attribute');
            $attribute_handler->createAndSave(array (
                'acode' => 'mediaImage',
                'dtid' => 'media--image',
                'module' => 'media',
                'name' => 'Image',
                'settings' => array (
                    'input' => array (
                        'method' => 'media--upload',
                        'extensions' => 'jpg jpeg',
                    ),
                ),
                'isLocked' => TRUE,
            ));

        endif;
        
    }
    
}