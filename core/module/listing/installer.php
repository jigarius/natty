<?php

namespace Module\Listing;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__listing_list',
            'description' => 'List data.',
            'columns' => array (
                'lid' => array (
                    'description' => 'List ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'lcode' => array (
                    'description' => 'List Code',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'sdata' => array (
                    'description' => 'Serialized data.',
                    'type' => 'blob',
                ),
                'status' => array (
                    'description' => '0 = disabled, 1 = enabled.',
                    'type' => 'int',
                    'length' => 2,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('lid'),
                ),
                'lid' => array (
                    'columns' => array ('lcode'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__listing_list_i18n',
            'description' => 'List data.',
            'columns' => array (
                'lid' => array (
                    'description' => 'List ID',
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
                'description' => array (
                    'description' => 'A brief description.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('lid', 'ail'),
                ),
            ),
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__listing_list');
        $schema_helper->dropTable('%__listing_list_i18n');
        
    }
    
}