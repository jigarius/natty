<?php

namespace Module\Example\Logic;

use Module\System\Classes\SerialHelper;

class DbalBasicsController {
    
    /**
     * Using the Natty DBO and performing basic CRUD operations.
     * @return type
     */
    public static function pageConnecting() {
        
        // Get the default database connection
        $dbo = Natty::getDbo();
        $tablename = '%__example_school';

        /**
         * The app must verify if it supports the given driver; In certain drivers,
         * certain queries have to be framed very differently; Ex: there is no OFFSET
         * and LIMIT clause in MsSQL
         */
        if ( 'mysql' != $dbo->getAttribute(\PDO::ATTR_DRIVER_NAME) )
            die ('Error: Databse driver not supported!');

        // Notice how the tablenames have been escaped;
        $query = "SELECT COUNT(*) FROM {%__country} WHERE 1=1";
        $result = $dbo->query($query);

        if ( false === $result ):
            echo 'An error has ocurred: ';
            natty_debug($dbo->errorInfo());
        endif;
        echo $result->fetchColumn() . ' number of row(s) were found in the country table!<br />';

        natty_debug();
        
    }
    
    public static function pageCrud() {
        
        /**
         * Inserting a record
         */
        $record = array (
            'name' => 'DBAL Test School',
            'description' => 'This is a test school created by the database example.',
            'status' => 1,
        );
        $record_id = $dbo->insert($tablename, $record);
        //natty_debug($record_id);

        /**
         * Updating a record
         */

        // Update a record by specifying conditions
        $record['description'] = 'Description update one.';
        $dbo->update($tablename, $record, array (
            'conditions' => array ('sid', '=', ':sid'),
            'parameters' => array ('sid' => $record_id),
        ));
        //natty_debug($record);

        // Update record by specifying a key-value row identifier. The code below
        // will upate the row where {sid} = $record_id
        $record['description'] = 'Description update two.';
        $dbo->update($tablename, $record, array (
            'key' => array ('sid' => $record_id),
        ));
        //natty_debug($record);

        // Update record by specifying identifiers which exist in the record itself.
        // The code below will treat the "sid" index of the record as an identifier
        // and update the record where {sid} = $record['sid']
        $record['sid'] = $record_id;
        $record['description'] = 'Description update three.';
        $dbo->update($tablename, $record, array (
            'keys' => array ('sid'),
        ));
        //natty_debug($record);

        /**
         * Deleting a record
         */

        // Deleting a record by specifying conditions
        $dbo->delete($tablename, array (
            'conditions' => array ('name', '=', ':name'),
            'parameters' => array ('name' => $record['name']),
        ));
        //natty_debug();

        // Deleting a record by specifying a key-value row identifier. Works just like
        // we used it in update above. The code below will delete any row where
        // {name} = "DBAL Test School".
        $dbo->delete($tablename, array (
            'key' => array (
                // Delete by name
                'name' => 'DBAL Test School',
            ),
        ));
        //natty_debug();

        /**
         * Upserting data
         */
        $record = array (
            'name' => 'DBAL Upsert School',
            'description' => 'This is the inserted record.',
            'status' => 0,
        );

        // Record would be inserted if it does not exist in database
        $dbo->upsert($tablename, $record, array (
            'keys' => array ('name')
        ));
        //natty_debug();

        // Record would be updated (as it already exists)
        $record['description'] = 'This is the updated record.';
        $record['status'] = 1;
        $dbo->upsert($tablename, $record, array (
            'keys' => array ('name'),
        ));
        //natty_debug();

        // Delete this test record (:
        $dbo->delete($tablename, array (
            'key' => array ('name' => $record['name']),
        ));
        
        // Prepare output
        $output = highlight_file(__FILE__, TRUE);
        return $output;
        
    }
    
    public static function pageSerials() {
        
        $sequence = 'example';

        // Get the next number in the sequence
        $serial = SerialHelper::generate($sequence);

        // Restart sequence?
        if ( $serial >= 10 )
            SerialHelper::delete($sequence);

        // Prepare document
        $output = array (
            '<p>The sequence <strong>example</strong> now has a count of <strong>' . $serial . '</strong>.'
            . ' The count would be restarted at <strong>10</strong>.</p>'
        );
        return $output;
        
    }
    
    public static function schema() {
        
        // Get the SchemaHelper
        $helper = \Natty::getDbo()->getSchemaHelper();
        $tablename = '%__example_foobar';

        // Delete table first
        $helper->dropTable($tablename);

        // Now create a fresh table
        $definition = array (
            'module' => 'example',
            'object' => $tablename,
            'description' => 'Stores nothing!',
            'fields' => array (
                'id' => array (
                    'description' => 'Auto ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('increment', 'unsigned')
                ),
                'code' => array (
                    'description' => 'Unique code',
                    'type' => 'varchar',
                    'length' => 32
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 128,
                ),
                'description' => array (
                    'type' => 'text',
                    'flags' => array ('nullable')
                ),
                'status' => array (
                    'description' => 'Status flag',
                    'type' => 'int',
                    'length' => 1,
                    'length' => 1,
                    'default' => 1,
                )
            ),
            'indexes' => array (
                'primary' => array ( 'id' ),
                'unique' => array (
                    'code' => array ( 'code' )
                ),
                'nonunique' => array (
                    'name' => array ( 'name' )
                )
            )
        );

        $helper->createTable($definition);

        // Use table definition
        $definition = $helper->readTable($tablename);

        // Drop the table
        $helper->dropTable($tablename);

        natty_debug();
        
    }
    
}