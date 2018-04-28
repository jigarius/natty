<?php

namespace Module\Payrec;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->createTable(array (
            'name' => '%__payrec_method',
            'description' => 'Method data.',
            'columns' => array (
                'mid' => array (
                    'description' => 'Method ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'type' => array (
                    'type' => 'varchar',
                    'lenth' => 8,
                ),
                'module' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'sdata' => array (
                    'description' => 'Serialized data.',
                    'type' => 'blob',
                ),
                'status' => array (
                    'description' => '0 = disabled, 1 = enabled.',
                    'type' => 'int',
                    'default' => 0,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('mid'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__payrec_method_i18n',
            'description' => 'Method i18n data.',
            'columns' => array (
                'mid' => array (
                    'description' => 'Method ID.',
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
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('mid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__payrec_tran',
            'description' => 'Payment transaction data.',
            'columns' => array (
                'tid' => array (
                    'description' => 'Transaction ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'tcode' => array (
                    'description' => 'Transaction code.',
                    'type' => 'varchar',
                    'length' => 32,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'mid' => array (
                    'description' => 'Method ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'contextType' => array (
                    'description' => 'Context in which the payment was made.',
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'contextId' => array (
                    'description' => 'An identifier associated with the context.',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'contextLabel' => array (
                    'description' => 'A human readable name for the context.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'contextUrl' => array (
                    'description' => 'A URL to the context object.',
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'description' => array (
                    'description' => 'A brief narration for the transaction.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'variables' => array (
                    'description' => 'Values to be replaced in the description placeholders.',
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'amount' => array (
                    'type' => 'decimal',
                    'length' => '20,3',
                    'flags' => array ('unsigned'),
                ),
                'idCreator' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'creatorName' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'creatorEmail' => array (
                    'type' => 'varchar',
                    'length' => 128,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'creatorMobile' => array (
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'idVerifier' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'dtCreated' => array (
                    'description' => 'Date and time of creation.',
                    'type' => 'datetime',
                ),
                'dtVerified' => array (
                    'description' => 'Date and time of verification.',
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'isStatusNotified' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'status' => array (
                    'description' => '0 = Processing, 1 = Successful, -1 = Failed.',
                    'type' => 'int',
                    'length' => 2,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid'),
                ),
                'tcode' => array (
                    'columns' => array ('tcode'),
                    'unique' => 1,
                ),
                'context' => array (
                    'columns' => array ('contextType', 'contextId'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__payrec_check_data',
            'description' => 'Check payment data.',
            'columns' => array (
                'ccode' => array (
                    'description' => 'Check code / number',
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'tid' => array (
                    'description' => 'Transaction ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'dtIssued' => array (
                    'description' => 'Date of issue',
                    'type' => 'date',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'bankName' => array (
                    'description' => 'Bank on which the check was drawn.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'amount' => array (
                    'description' => 'Amount of the check.',
                    'type' => 'decimal',
                    'length' => '20,3',
                ),
                'description' => array (
                    'description' => 'Other details like payee name, etc.',
                    'type' => 'text',
                    'default' => NULL,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__payrec_wire_data',
            'description' => 'Wire payment data.',
            'columns' => array (
                'wcode' => array (
                    'description' => 'Wire transaction ID',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'tid' => array (
                    'description' => 'Transaction ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'amount' => array (
                    'description' => 'Amount of the transfer.',
                    'type' => 'decimal',
                    'length' => '20,3',
                ),
                'description' => array (
                    'description' => 'Other details like bank name, etc.',
                    'type' => 'text',
                    'default' => NULL,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid'),
                ),
            ),
        ));
        
        parent::install();
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->dropTable('%__payrec_method');
        $schema_helper->dropTable('%__payrec_method_i18n');
        
        $schema_helper->dropTable('%__payrec_transaction');
        $schema_helper->dropTable('%__payrec_check_data');
        
    }
    
}