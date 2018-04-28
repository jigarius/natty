<?php

namespace Module\Example\Logic;

use Natty\Helper\FileCacheHelper;
use Natty\Helper\DatabaseCacheHelper;

class DefaultController {
    
    public static function pageBasics() {
        
        /**
         * Generating a global route from any module to any module
         */
        $route = \Natty::url('example', array (
            'foo' => 'bar',
            'baz' => 'fish'
        ), array (
            'base' => 'absolute',
            'fragment' => 'content'
        ));
        
    }
    
    public static function pageCaching() {
        
        $time_format = 'H:i:s a';
        $output = array ();

        // Add current time for comparison
        $output[] = '<p><strong>Current Time:</strong> ' . date($time_format) . '</p>';

        /**
         * File cache helper
         * To use file caching, write permission must be enabled on certain
         * storage directories within the instance root.
         */
        $time = FileCacheHelper::read('example', 'time', 30);
        if ( !$time ):
            $time = date($time_format);
            FileCacheHelper::write('example', 'time', $time);
        endif;
        $output[] = '<p><strong>File Cache:</strong> ' . $time . ' (recached every 30 seconds).</p>';

        /**
         * Database cache helper
         * To use database caching, cache tables should be created at the
         * time of module installation.
         */

        // Bin creation code
        if (0):
            DatabaseCacheHelper::createBin('example');
        endif;

        $time = DatabaseCacheHelper::read('example', 'time', 30);
        if ( !$time ):
            $time = date($time_format);
            DatabaseCacheHelper::write('example', 'time', $time);
        endif;
        $output[] = '<p><strong>Database Cache:</strong> ' . $time . ' (recached every 30 seconds).</p>';
        
        return $output;
        
    }
    
    public static function pagePaging() {
        
        $command = \Natty::getCommand();
        $location = \Natty::url($command);

        // Prepare demo paging data; If you can see demo data, change this to false
        if ( isset ($_REQUEST['populate']) ):
            natty_debug();
            $table = \Natty::getDbo()->getTable('%__example_school');
            $table->truncate();
            for ( $i = 1; $i <= 100; $i++ ):
                $table->insert(array (
                    'name' => 'School ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'description' => uniqid(),
                    'status' => 1
                ));
            endfor;
            \Natty::getResponse()->redirect($location);
        endif;

        // Prepare SelectQuery
        $query = \Natty::getDbo()
                ->getQuery('SELECT', '%__example_school es')
                ->addColumns(array ('sid', 'name', 'description', 'status'), 'es');

        // Create the list object
        $paging_helper = new Natty\Helper\PagingHelper($query);

        // Map database fieldnames with url parameters
        $list_head = array (
            // System parameter: Chunk Size. This parameter determines the number of
            // records to display per page (defaults to 24). If set to zero, the list 
            // will load all records without the LIMIT clause.
            'cs' => array (
                // This is the default value for number of items to be displayed in
                // on page, i.e. chunk-size
                '_value' => 24,
                // Parameter value can be overridden by the REQUEST. This parameter is
                // locked by default. Specify if you want to unlock it.
                '_isLocked' => 0,
            ),
            // System parameter: Start Index. This parameter determines the index from
            // which records would be displayed on this page (defaults to 0).
            'si' => array (
                '_value' => 0,
                // Parameter value can be overridden by the REQUEST. This parameter is
                // unlocked by default. Only specify if you want to lock it.
                '_isLocked' => 0,
            ),
            // Custom parameters
            'sid' => array (
                // The column to which the parameter is bound
                '_column' => 'es.sid',
                '_data' => 'ID',
                // Set sortability options for this field
                '_sortEnabled' => 1,
                '_sortLocked' => 0,
            ),
            'name' => array (
                '_data' => 'Name',
                '_column' => 'es.name',
                '_filterEnabled' => 1,
                '_filterLocked' => 0,
            ),
            'description' => array (
                '_data' => 'Description',
                '_column' => 'es.description'
            ),
            'status' => array (
                '_data' => 'Status',
                '_column' => 'es.status'
            ),
        );
        $paging_helper->setParameters($list_head);

        // Retrieve list data
        $list_data = $paging_helper->execute();

        // Prepare output
        $output = array ();
        $output[] = array (
            '_render' => 'toolbar',
            '_right' => '<a href="' . \Natty::url($command, array (
                'populate' => 1,
            )) . '" class="k-button">Re-Populate Data</a>',
        );
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_data['items'],
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
        );
        $output[] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageYaml() {
        
        Importer::import('Spyc\\Spyc');

        /**
         * Decoding YAML
         */
        $filename = \Natty::path('library/config/system.yml', 'real');
        $yaml = Spyc::YAMLLoad();

        /**
         * Encoding YAML
         */
        $yaml = Spyc::YAMLDump(array (
            'description' => 'Contains data about students in a school',
            'fields' => array (
                'esid' => array (
                    'type' => 'int',
                    'size' => 'big',
                    'description' => 'Unique ID for a student',
                    'null' => false
                )
            ),
            'primarykey' => array ('esid')
        ));
        
    }
    
}