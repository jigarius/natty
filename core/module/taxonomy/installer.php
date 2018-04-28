<?php

namespace Module\Taxonomy;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__taxonomy_group',
            'columns' => array (
                'gid' => array (
                    'description' => 'Group ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'gcode' => array (
                    'description' => 'Group Code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'module' => array (
                    'description' => 'FK: system_package.code',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                    'default' => 0,
                ),
                'maxLevels' => array (
                    'description' => 'Maximum depth of the term tree.',
                    'type' => 'int',
                    'length' => 2,
                    'flags' => array ('unsigned')
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('gid'),
                ),
                'gcode' => array (
                    'columns' => array ('gcode'),
                    'unique' => TRUE,
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__taxonomy_group_i18n',
            'columns' => array (
                'gid' => array (
                    'description' => 'Group ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'ail' => array (
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('gid', 'ail')
                ),
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__taxonomy_term',
            'columns' => array (
                'tid' => array (
                    'description' => 'Term ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'module' => array (
                    'description' => 'FK: system_package.code',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'gid' => array (
                    'description' => 'Group ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'gcode' => array (
                    'description' => 'Group Code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'parentId' => array (
                    'description' => 'Parent ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'ooa' => array (
                    'description' => 'Order of Appearance',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'level' => array (
                    'description' => 'The vertical depth of the term in the tree.',
                    'type' => 'int',
                    'length' => 2,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid'),
                ),
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__taxonomy_term_i18n',
            'columns' => array (
                'tid' => array (
                    'description' => 'Term ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid', 'ail'),
                ),
            )
        ));

        // Default settings
        \Natty::writeSetting('taxonomy--maxTermLevel', 5);
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__taxonomy_group');
        $schema_helper->dropTable('%__taxonomy_group_i18n');
        $schema_helper->dropTable('%__taxonomy_term');
        $schema_helper->dropTable('%__taxonomy_term_i18n');
        
    }
    
}