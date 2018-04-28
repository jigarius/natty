<?php

namespace Module\People;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__people_user',
            'description' => 'User accounts.',
            'columns' => array (
                'uid' => array (
                    'description' => 'User ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment')
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 128,
                    'flags' => array ('nullable')
                ),
                'email' => array (
                    'type' => 'varchar',
                    'length' => 128,
                    'default' => null,
                    'flags' => array ('nullable')
                ),
                'alias' => array (
                    'type' => 'varchar',
                    'length' => 32,
                    'default' => null,
                    'flags' => array ('nullable')
                ),
                'hash' => array (
                    'type' => 'varchar',
                    'length' => 128,
                    'flags' => array ('nullable')
                ),
                'tzid' => array (
                    'type' => 'varchar',
                    'description' => 'Timezone of the user.',
                    'length' => 32,
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'idLanguage' => array (
                    'type' => 'varchar',
                    'description' => 'Language of the user; FK: system_language.lid',
                    'default' => NULL,
                    'length' => 8,
                    'flags' => array ('nullable')
                ),
                'idCurrency' => array (
                    'type' => 'varchar',
                    'description' => 'Currency of the user; FK: system_currency.cid',
                    'default' => NULL,
                    'length' => 3,
                    'flags' => array ('nullable')
                ),
                'gtp' => array (
                    'type' => 'varchar',
                    'description' => 'Guess the password.',
                    'length' => 16,
                    'flags' => array ('nullable')
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'dtModified' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'dtAccessed' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'dtPasswordChanged' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'sdata' => array (
                    'description' => 'Serialized data.',
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'status' => array (
                    'description' => '1 = Enabled; 0 = Disabled; -1 = Hidden.',
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('uid')
                ),
                'email' => array (
                    'columns' => array ('email'),
                    'unique' => 1
                ),
                'alias' => array (
                    'columns' => array ('alias'),
                    'unique' => 1
                )
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__people_role',
            'description' => 'User roles.',
            'columns' => array (
                'rid' => array (
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment')
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 32
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                    'flags' => array ('unsigned')
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid')
                ),
                'name' => array (
                    'columns' => array ('name'),
                    'unique' => 1
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__people_role_permission',
            'description' => 'Actions which roles are permitted to do.',
            'columns' => array (
                'rid' => array (
                    'description' => 'FK: people_role.rid',
                    'type' => 'int',
                    'length' => 10
                ),
                'aid' => array (
                    'description' => 'FK: system_action.aid',
                    'type' => 'varchar',
                    'length' => 64
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid', 'aid'),
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__people_user_role_map',
            'description' => 'Roles assigned to various users.',
            'columns' => array (
                'uid' => array (
                    'description' => 'FK: people_user.uid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned')
                ),
                'rid' => array (
                    'description' => 'FK: people_role.rid',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned')
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('uid', 'rid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__people_token',
            'description' => 'User token data.',
            'columns' => array (
                'tid' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'uid' => array (
                    'description' => 'FK: people_user.uid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned')
                ),
                'purpose' => array (
                    'description' => 'Purpose of the token.',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'sdata' => array (
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'dtCreated' => array (
                    'description' => 'Time of creation.',
                    'type' => 'datetime',
                ),
                'dtExpired' => array (
                    'description' => 'Time of expiry.',
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid')
                ),
                'uid-purpose' => array (
                    'columns' => array ('uid', 'purpose'),
                    'unique' => 1,
                ),
            ),
        ));

        // If no home route is defined, add home route
        if ( !\Natty::readSetting('system--routeDefault') )
            \Natty::writeSetting('system--routeDefault', 'sign-in');
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->dropTable('%__people_user');
        $schema_helper->dropTable('%__people_role');
        $schema_helper->dropTable('%__people_role_permission');
        $schema_helper->dropTable('%__people_user_role_map');
        $schema_helper->dropTable('%__people_token');
        
    }
    
    public static function enable() {
        
        // First run
        if ( \Natty::readSetting('people--installing') ):

            // Create default roles
            $role_handler = \Natty::getHandler('people--role');

            // Create admin role
            $role_coll[1] = $role_handler->createAndSave(array (
                'isNew' => 1,
                'rid' => 1,
                'name' => 'Administrator',
                'isLocked' => 1,
                'status' => 1,
            ));

            // Create anonymous role
            $role_coll[2] = $role_handler->createAndSave(array (
                'isNew' => 1,
                'rid' => 2,
                'name' => 'Anonymous',
                'isLocked' => 1,
                'status' => 1,
            ));

            // Create member role
            $role_coll[3] = $role_handler->createAndSave(array (
                'isNew' => 1,
                'rid' => 3,
                'name' => 'Member',
                'isLocked' => 1,
                'status' => 1,
            ));

            // Create a root user
            $user_handler = \Natty::getHandler('people--user');

            // Create anonymous user
            $anon_user = $user_handler->readById(0);
            if ( !$anon_user ):

                $anon_user = $user_handler->createAndSave(array (
                    'isNew' => TRUE,
                    'uid' => 0,
                    'name' => 'Anonymous',
                    'alias' => 'anonymous',
                    'hash' => NULL,
                    'status' => 1,
                ));

                // User ID for anonymous user will be zero
                $user_handler->getDbo()->update('%__people_user', array (
                    'uid' => 0,
                ), array (
                    'key' => array ('uid' => $anon_user->uid)
                ));
                $anon_user->uid = 0;

                // Assign anonymous role
                \Natty::getDbo()->insert('%__people_user_role_map', array (
                    'uid' => 0,
                    'rid' => 2,
                ));

            endif;

            // Create root user, if not exists
            $user_root = $user_handler->readById(1);
            if ( !$user_root ):

                $user_root = $user_handler->createAndSave(array (
                    'isNew' => TRUE,
                    'uid' => 1,
                    'name' => 'Root',
                    'alias' => 'root',
                    'password' => 'password',
                    'status' => 1,
                ));

                // Assign admin role
                \Natty::getDbo()->insert('%__people_user_role_map', array (
                    'uid' => $user_root->uid,
                    'rid' => 1,
                ));

                \Natty\Console::success('Root user has been created with password "' . $user_root->password . '"');

            endif;

        endif;
        
    }
    
}