<?php

namespace Module\Contact;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__contact_category',
            'description' => 'Contact category data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Category ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment')
                ),
                'recipients' => array (
                    'description' => 'Comma-separated email addresses of recipients.',
                    'type' => 'text',
                ),
                'ooa' => array (
                    'description' => 'Order of appearance.',
                    'type' => 'int',
                    'length' => 10,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid')
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__contact_category_i18n',
            'description' => 'Contact category i18n data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Category ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment')
                ),
                'ail' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8
                ),
                'name' => array (
                    'description' => 'A name for the category.',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'flags' => array ('unsigned'),
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'ail')
                ),
            )
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__contact_category');
        $schema_helper->dropTable('%__contact_category_i18n');
        
    }
    
}