<?php

namespace Module\Eav;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__eav_datatype',
            'description' => 'Data type declarations.',
            'columns' => array (
                'dtid' => array (
                    'description' => 'Data type ID',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'module' => array (
                    'description' => 'The module which installed this action',
                    'type' => 'varchar',
                    'length' => 32
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('dtid')
                )
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__eav_attribute',
            'description' => 'Attribute declarations.',
            'columns' => array (
                'aid' => array (
                    'description' => 'Attribute ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'acode' => array (
                    'description' => 'Attribute Code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'module' => array (
                    'description' => 'The module which installed this action',
                    'type' => 'varchar',
                    'length' => 32
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'dtid' => array (
                    'description' => 'FK: eav_datatype.dtid',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'nov' => array (
                    'description' => 'Number of Values',
                    'type' => 'int',
                    'length' => 4,
                ),
                'sdata' => array (
                    'type' => 'blob',
                ),
                'isConfigured' => array (
                    'description' => 'Whether basic configuration has been completed.',
                    'type' => 'int',
                    'length' => 1,
                ),
                'isLocked' => array (
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
                    'columns' => array ('aid')
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__eav_attrinst',
            'description' => 'Attribute instances.',
            'columns' => array (
                'aiid' => array (
                    'description' => 'Attribute Instance ID',
                    'type' => 'int',
                    'flags' => array ('unsigned', 'increment'),
                ),
                'aid' => array (
                    'description' => 'FK: eav_attribute.aid',
                    'type' => 'int',
                    'length' => 10,
                ),
                'acode' => array (
                    'description' => 'FK: eav_attribute.acode - Copied for ease of access.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'etid' => array (
                    'description' => 'FK: system_entitytype.etid',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'egid' => array (
                    'description' => 'Entity Group ID',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'name' => array (
                    'description' => 'Human-readable name.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'nov' => array (
                    'description' => 'Number of Values',
                    'type' => 'int',
                    'length' => 4,
                ),
                'sdata' => array (
                    'type' => 'blob',
                ),
                'ooa' => array (
                    'description' => 'Order of Appearance',
                    'type' => 'int',
                ),
                'isLocked' => array (
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
                    'columns' => array ('aiid')
                ),
            )
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__eav_datatype');
        $schema_helper->dropTable('%__eav_attribute');
        $schema_helper->dropTable('%__eav_attrinst');
        
    }
    
    public static function enable() {
        
        if ( \Natty::readSetting('eav--installing') ):

            $attribute_handler = \Natty::getHandler('eav--attribute');

            // Create summary attribute
            $attribute_handler->createAndSave(array (
                'acode' => 'eavSummary',
                'dtid' => 'eav--text',
                'module' => 'eav',
                'name' => 'Summary',
                'description' => 'A brief summary for the object for display in previews.',
                'settings' => array (
                    'input' => array (
                        'method' => 'eav--textarea',
                        'rows' => 5,
                        'rte' => 1,
                    ),
                ),
                'isLocked' => 1,
            ));

            // Create description attribute
            $attribute_handler->createAndSave(array (
                'acode' => 'eavBody',
                'dtid' => 'eav--text',
                'module' => 'eav',
                'name' => 'Body',
                'description' => 'A detailed description of the object for full page display.',
                'settings' => array (
                    'input' => array (
                        'method' => 'eav--textarea',
                        'rows' => 20,
                        'rte' => 1,
                    ),
                ),
                'isLocked' => 1,
            ));

        endif;
        
    }
    
}