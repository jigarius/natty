<?php

namespace Module\System;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        // Register schema
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->createTable(array (
            'name' => '%__system_serial',
            'description' => 'Serial number counters for custom sequences.',
            'columns' => array (
                'sequence' => array (
                    'description' => 'The object to which the sequence applies.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'delta' => array (
                    'description' => 'The object identifier or group to which the sequence applies.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'count' => array (
                    'description' => 'The count.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sequence', 'delta'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__system_email',
            'description' => 'Email format data.',
            'columns' => array (
                'eid' => array (
                    'description' => 'Email ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('eid'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__system_email_i18n',
            'description' => 'Email i18n data.',
            'columns' => array (
                'eid' => array (
                    'description' => 'Email ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'ail' => array (
                    'description' => 'Language ID.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'subject' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('eid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__system_blockinst',
            'description' => 'Block instance data.',
            'columns' => array (
                'biid' => array (
                    'description' => 'Block Instance ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment')
                ),
                'bid' => array (
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'heading' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'description' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'visibility' => array (
                    'type' => 'blob',
                ),
                'sdata' => array (
                    'description' => 'Serialized data.',
                    'type' => 'blob',
                ),
                'status' => array (
                    'description' => 'Default status (overridden by exceptions).',
                    'type' => 'int',
                    'length' => 2,
                    'flags' => array ('unsigned'),
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 1
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('biid')
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name'  => '%__system_entitytype',
            'description' => 'Entity-type data.',
            'columns' => array (
                'etid' => array (
                    'description' => 'Entity-type ID',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'module' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'sdata' => array (
                    'description' => 'Serialized data',
                    'type' => 'text',
                ),
                'isAttributable' => array (
                    'type' => 'int',
                    'length' => 1
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('etid')
                )
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_language',
            'description' => 'Language data.',
            'columns' => array (
                    'lid' => array (
                        'description' => 'Language ID',
                        'type' => 'varchar',
                        'length' => 8
                    ),
                    'nativeName' => array (
                        'type' => 'varchar',
                        'length' => 255,
                    ),
                    'status' => array (
                        'type' => 'int',
                        'length' => 2,
                        'default' => 0,
                    )
                ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('lid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_currency',
            'description' => 'Currency data.',
            'columns' => array (
                    'cid' => array (
                        'description' => 'Currency ID',
                        'type' => 'varchar',
                        'length' => 8
                    ),
                    'nativeName' => array (
                        'type' => 'varchar',
                        'length' => 255,
                    ),
                    'xRate' => array (
                        'description' => 'Exchange rate as compared to site currency.',
                        'type' => 'decimal',
                        'length' => '10,3',
                        'default' => 1,
                        'flags' => array ('unsigned'),
                    ),
                    'sdata' => array (
                        'type' => 'blob',
                    ),
                    'status' => array (
                        'type' => 'int',
                        'length' => 2,
                        'default' => 0,
                    ),
                ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_incident',
            'description' => 'Incident data.',
            'columns' => array (
                'iid' => array (
                    'description' => 'Unique Incident ID',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'key' => array (
                    'description' => 'An identifier associated with this incident. Example, a username or an IP Address.',
                    'type' => 'varchar',
                    'length' => 128,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'idCreator' => array (
                    'description' => 'The user who triggered this incident.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'remoteIp' => array (
                    'description' => 'The IP from which the incident was triggered.',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'type' => array (
                    'description' => 'Nature of the incident.',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'description' => array (
                    'description' => 'Description of the incident with placeholders.',
                    'type' => 'text',
                ),
                'variables' => array (
                    'description' => 'Values to be replaced in the description placeholders.',
                    'type' => 'blob',
                ),
                'originUrl' => array (
                    'description' => 'The URL at which the incident took place.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'refererUrl' => array (
                    'description' => 'The URL lead to the incident origin URL.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'tsCreated' => array (
                    'description' => 'The time at which the incident ocurred.',
                    'type' => 'timestamp',
                    'flags' => array ('unsigned'),
                ),
                'tsExpired' => array (
                    'description' => 'The time after which this incident may be ignored and deleted.',
                    'type' => 'timestamp',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('iid'),
                ),
                'tsCreated' => array (
                    'columns' => array ('tsCreated'),
                ),
                'type' => array (
                    'columns' => array ('type')
                ),
                'remoteIp' => array (
                    'columns' => array ('remoteIp')
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_package',
            'description' => 'Package data.',
            'columns' => array (
                'pid' => array (
                    'description' => 'Package ID',
                    'type' => 'varchar',
                    'length' => 36,
                ),
                'code' => array (
                    'description' => 'Package code',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'type' => array (
                    'description' => 'Package type',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'version' => array (
                    'description' => 'Package type',
                    'type' => 'decimal',
                    'length' => '3,1',
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => '255',
                ),
                'description' => array (
                    'type' => 'varchar',
                    'length' => '512',
                    'flags' => array ('nullable'),
                    'default' => NULL,
                ),
                'sdata' => array (
                    'description' => 'Serialized data.',
                    'type' => 'text',
                    'flags' => array ('nullable'),
                    'default' => NULL,
                ),
                'path' => array (
                    'description' => 'Path of the package within the application.',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'tsCreated' => array (
                    'type' => 'int',
                    'length' => 11,
                ),
                'ooa' => array (
                    'description' => 'Order of appearance.',
                    'type' => 'int',
                    'length' => 5,
                    'default' => 999,
                    'flags' => array ('unsigned'),
                ),
                'isSystem' => array (
                    'type' => 'int',
                    'length' => 1,
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 1,
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('pid')
                ),
                'type-code' => array (
                    'columns' => array ('type', 'code'),
                    'unique' => TRUE,
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_route',
            'description' => 'Routes',
            'columns' => array (
                'rid' => array (
                    'description' => 'Route ID',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'module' => array (
                    'description' => 'The module declaring the route.',
                    'type' => 'varchar',
                    'length' => 36,
                ),
                'heading' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'size' => array (
                    'description' => 'Number of parts in the route.',
                    'type' => 'int',
                    'length' => 2
                ),
                'variables' => array (
                    'description' => 'Number of variables in the route.',
                    'type' => 'int',
                    'length' => 2,
                ),
                'parentId' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'ooa' => array (
                    'type' => 'int',
                    'length' => 10,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'sdata' => array (
                    'description' => 'Serialized data',
                    'type' => 'text',
                ),
                'isBackend' => array (
                    'description' => 'Whether it is an back-end route.',
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_settings',
            'description' => 'System settings.',
            'columns' => array (
                'sid' => array (
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'value' => array (
                    'type' => 'text',
                ),
                'isSerialized' => array (
                    'type' => 'int',
                    'length' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_text',
            'description' => 'Text translations',
            'columns' => array (
                'lid' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'pid' => array (
                    'description' => 'Package ID',
                    'type' => 'varchar',
                    'length' => 36,
                ),
                'bundle' => array (
                    'description' => 'The collection to which the text belongs',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'hash' => array (
                    'description' => 'An md5 hash of the text for recognition',
                    'type' => 'char',
                    'length' => 32
                ),
                'text' => array (
                    'description' => 'The text translation in the given language',
                    'type' => 'text',
                    'flags' => array ('nullable')
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('lid', 'pid', 'bundle', 'hash')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__system_rewrite',
            'description' => 'URL rewrite data.',
            'columns' => array (
                'rid' => array (
                    'description' => 'Rewrite ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('increment'),
                ),
                'systemPath' => array (
                    'description' => 'System URL being re-written.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'customPath' => array (
                    'description' => 'Custom URL aliasing the system URL.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'ail' => array (
                    'description' => 'Language ID.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid')
                ),
                'customPath' => array (
                    'columns' => array ('customPath'),
                    'unique' => 1,
                ),
            ),
        ));

        // Create cache bin
        \Natty\Helper\DatabaseCacheHelper::createBin('system');
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__system_blockinst');
        $schema_helper->dropTable('%__system_entitytype');
        $schema_helper->dropTable('%__system_language');
        $schema_helper->dropTable('%__system_incident');
        $schema_helper->dropTable('%__system_package');
        $schema_helper->dropTable('%__system_route');
        $schema_helper->dropTable('%__system_incident');
        $schema_helper->dropTable('%__system_currency');
        $schema_helper->dropTable('%__system_settings');
        $schema_helper->dropTable('%__system_text');
        $schema_helper->dropTable('%__system_rewrite');
        
    }
    
    public static function enable() {
        
        $lang_handler = \Natty::getHandler('system--language');

        // Create undefined language
        if ( !$lang = $lang_handler->readById('UNDF') ):
            $lang = $lang_handler->createAndSave(array (
                'isNew' => 1,
                'lid' => 'UNDF',
                'nativeName' => 'Undefined',
            ));
        endif;

        // Create english language
        if ( !$lang = $lang_handler->readById('en-US') ):
            $lang = $lang_handler->createAndSave(array (
                'isNew' => 1,
                'lid' => 'en-US',
                'nativeName' => 'English - United States',
            ));
        endif;

        // Default site name
        if ( !\Natty::readSetting('system--siteName') )
            \Natty::writeSetting('system--siteName', $_SERVER['SERVER_NAME']);

        // Default site language
        if ( !\Natty::readSetting('system--language') )
            \Natty::writeSetting('system--language', 'en-US');

        // Default email method
        if ( !\Natty::readSetting('system--mailMethod') )
            \Natty::writeSetting('system--mailMethod', 'sendmail');
        
    }
    
}