<?php

namespace Module\Example;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $helper_schema = \Natty::getDbo()->getSchemaHelper();
        
        $helper_schema->createTable(array (
            'name' => '%__example_student',
            'description' => 'Contains student data',
            'columns' => array (
                'sid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'description' => 'Unique ID',
                    'flags' => array ('increment')
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 64
                ),
                'description' => array (
                    'type' => 'text',
                    'length' => 64
                ),
                'tsCreated' => array (
                    'description' => 'Time created',
                    'type' => 'timestamp',
                    'default' => 0
                ),
                'tsModified' => array (
                    'description' => 'Time modified',
                    'type' => 'timestamp',
                    'default' => 0
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 1
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sid')
                )
            )
        ));
        $helper_schema->createTable(array (
            'name' => '%__example_course',
            'description' => 'Contains course data.',
            'columns' => array (
                'cid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'description' => 'Unique ID',
                    'flags' => array ('increment')
                ),
                'code' => array (
                    'type' => 'varchar',
                    'length' => 32,
                    'flags' => array ()
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 1
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid')
                ),
                'code' => array (
                    'columns' => array ('code'),
                    'unique' => 1
                )
            )
        ));
        $helper_schema->createTable(array (
            'name' => '%__example_course_i18n',
            'description' => 'Contains course i18n data.',
            'columns' => array (
                'cid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'description' => 'Unique ID',
                    'flags' => array ('increment')
                ),
                'ail' => array (
                    'type' => 'varchar',
                    'length' => 8,
                    'description' => 'As in language',
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'description' => array (
                    'type' => 'text',
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'ail')
                )
            )
        ));

        // Create cache bins
        \Natty\Helper\DatabaseCacheHelper::createBin('example');
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__example_student');
        
        $schema_helper->dropTable('%__example_course');
        $schema_helper->dropTable('%__example_course_i18n');
        
    }
    
}