<?php

namespace Natty\Helper;

/**
 * Database Cache Helper
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class DatabaseCacheHelper {

    /**
     * Returns the name of database table for the said bin.
     * @param string $bin Storage bin name - starts with module namespace.
     * @return string Tablename
     */
    protected static function getStorageTablename($bin) {
        $tablename = '%__cache_' . str_replace('/', '_', $bin);
        return $tablename;
    }
    
    protected static function getStorageIdentifier($key) {
        return is_array($key) ? implode(':', $key) : $key;
    }
    
    public static function write($bin, $key, $data) {
        
        $dbo = \Natty::getDbo();
        $tablename = self::getStorageTablename($bin);
        $identifier = self::getStorageIdentifier($key);
        
        self::delete($bin, $key);
        
        // Insert new record
        $record = array (
            'key' => $identifier,
            'data' => is_string($data) ? $data : serialize($data),
            'isSerialized' => !is_string($data),
            'tsCreated' => time(),
        );
        
        $dbo->insert($tablename, $record);
        
    }

    public static function read($bin, $key, $ttl = NULL) {
        
        $dbo = \Natty::getDbo();
        $tablename = self::getStorageTablename($bin);
        $identifier = self::getStorageIdentifier($key);
        
        // Read existing record
        $output = $dbo->read($tablename, array (
            'key' => array (
                'key' => $identifier,
            ),
            'unique' => 1,
        ));
        
        if ( !$output )
            return NULL;
        
        // See if record is valid
        if ($ttl):
            $age = time() - $output['tsCreated'];
            if ( $age > $ttl )
                return NULL;
        endif;
        
        // Return data from cache
        $data = $output['isSerialized']
            ? unserialize($output['data']) : $output['data'];
        
        return $data;
        
    }

    public static function delete($bin, $key = NULL) {
        
        $dbo = \Natty::getDbo();
        $tablename = self::getStorageTablename($bin);
        $identifier = self::getStorageIdentifier($key);
        
        // Delete existing record
        $dbo->delete($tablename, array (
            'key' => array (
                'key' => $key
            ),
        ));
        
    }
    
    public static function createBin($bin) {
        
        $tablename = self::getStorageTablename($bin);
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        if ( !$schema_helper->readTable($tablename) ):
            $schema_helper->createTable(array (
                'description' => 'Cache storage for bin "' . $bin . '".',
                'name' => $tablename,
                'columns' => array (
                    'key' => array (
                        'type' => 'varchar',
                        'length' => 128,
                    ),
                    'data' => array (
                        'description' => 'Serialized data.',
                        'type' => 'blob',
                        'size' => 'long',
                    ),
                    'isSerialized' => array (
                        'type' => 'int',
                        'length' => 2,
                        'default' => 0,
                        'flags' => array ('unsigned'),
                    ),
                    'tsCreated' => array (
                        'type' => 'int',
                        'length' => 16,
                    ),
                ),
                'indexes' => array (
                    'primary' => array (
                        'columns' => array ('key'),
                    )
                ),
            ));
        endif;
        
    }
    
    public static function truncateBin($bin) {
        
        $tablename = self::getStorageTablename($bin);
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->truncateTable($tablename);
        
    }
    
    public static function destroyBin($bin) {
        
        $tablename = self::getStorageTablename($bin);
        $schema_helper = \Natty::getDbo()->getSchemaHelper();
        
        $schema_helper->dropTable($tablename);
        
    }

}