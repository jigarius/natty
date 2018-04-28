<?php

namespace Module\Location;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->createTable(array (
            'name' => '%__location_country',
            'description' => 'Country data.',
            'columns' => array (
                'cid' => array (
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'isoNumCode' => array (
                    'type' => 'int',
                    'length' => 4,
                ),
                'iso2Code' => array (
                    'type' => 'varchar',
                    'length' => 2,
                ),
                'nativeName' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'flags' => array (),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid'),
                ),
                'isoNumCode' => array (
                    'columns' => array ('isoNumCode'),
                    'unique' => 1,
                ),
                'iso2Code' => array (
                    'columns' => array ('iso2Code'),
                    'unique' => 1,
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__location_country_i18n',
            'description' => 'Country i18n data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Country ID',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'ail' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'flags' => array (),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'ail'),
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__location_state',
            'description' => 'State data.',
            'columns' => array (
                'sid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'scode' => array (
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'cid' => array (
                    'description' => 'Country ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'nativeName' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'flags' => array (),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sid'),
                ),
                'scode' => array (
                    'columns' => array ('scode'),
                    'unique' => 1,
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__location_state_i18n',
            'description' => 'State i18n data.',
            'columns' => array (
                'sid' => array (
                    'type' => 'int',
                    'length' => 20,
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
                    'flags' => array (),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sid', 'ail'),
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__location_region',
            'description' => 'Region data.',
            'columns' => array (
                'rid' => array (
                    'description' => 'Region ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'cid' => array (
                    'description' => 'Country ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'sid' => array (
                    'description' => 'State ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'hasPostCodeData' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid'),
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__location_region_i18n',
            'description' => 'Region i18n data.',
            'columns' => array (
                'rid' => array (
                    'description' => 'Region ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'description' => 'Language ID.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'flags' => array (),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('rid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__location_regionitem',
            'description' => 'Items in region scope.',
            'columns' => array (
                'rid' => array (
                    'description' => 'Region ID',
                    'type' => 'int',
                    'flags' => array ('unsigned'),
                ),
                'fromPostCode' => array (
                    'description' => 'Postal code range start.',
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'tillPostCode' => array (
                    'description' => 'Postal code range end.',
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'rid' => array (
                    'columns' => array ('rid'),
                ),
            )
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__location_useraddress',
            'description' => 'User address data.',
            'columns' => array (
                'aid' => array (
                    'description' => 'Address ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'uid' => array (
                    'description' => 'User ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'name' => array (
                    'description' => 'A label for reference.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'body' => array (
                    'description' => 'Building, street and locality data.',
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'landmark' => array (
                    'description' => 'Nearest landmark.',
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'postCode' => array (
                    'description' => 'Postal code.',
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'city' => array (
                    'description' => 'Name of the city, county or union.',
                    'type' => 'varchar',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'cid' => array (
                    'description' => 'Country ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'sid' => array (
                    'description' => 'State ID.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'rid' => array (
                    'description' => 'Region ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('aid'),
                ),
            ),
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__location_country');
        $schema_helper->dropTable('%__location_country_i18n');
        
        $schema_helper->dropTable('%__location_state');
        $schema_helper->dropTable('%__location_state_i18n');
        
        $schema_helper->dropTable('%__location_region');
        $schema_helper->dropTable('%__location_region_i18n');
        $schema_helper->dropTable('%__location_region_scope');
        
        $schema_helper->dropTable('%__location_address');
        
    }
    
    public static function enable() {
        
        // First run
        if ( \Natty::readSetting('location--installing') ):

            $package_location = \Natty::getHandler('system--package')->readById('mod-location', array (
                'nocache' => 1,
            ));

            // Import country data
            $country_handler = \Natty::getHandler('location--country');
            $filename = NATTY_ROOT . DS . $package_location->path . '/data/country-coll.en-us.csv';
            $fp = fopen($filename, 'r');
            $record_keys = fgetcsv($fp);
            while ( $record_values = fgetcsv($fp) ):

                $record = array_combine($record_keys, $record_values);
                $record['ail'] = 'en-US';

                $country_handler->createAndSave($record);

            endwhile;
            fclose($fp);

        endif;
        
    }
    
}