<?php

namespace Module\Listing\Classes;

class DatatypeHandler {
    
    protected static $cache = array ();
    
    public static function read(array $options = array ()) {
        
        $options = array_merge(array (
            'language' => \Natty::getOutputLangId(),
            'nocache' => FALSE,
        ), $options);
        
        $lid = $options['language'];
        
        if ( !isset(self::$cache[$lid]) || $options['nocache'] )
            self::rebuild($options);
        
        return self::$cache[$lid];
        
    }
    
    public static function readById($identifier, array $options = array ()) {
        
        $data = self::read($options);
        $output = isset ($data[$identifier])
            ? $data[$identifier] : FALSE;
        
        return $output;
        
    }
    
    public static function rebuild(array $options = array ()) {
        
        if ( !isset ($options['language']) )
            $options['language'] = \Natty::getOutputLangId ();
        $lid = $options['language'];
        
        // Trigger an event to collect data
        $data = array ();
        \Natty::trigger('listing--datatypeDeclare', $data);
        \Natty::trigger('listing--datatypeRevise', $data);
        
        foreach ( $data as $dtid => &$record ):
            
            $record = (object) $record;
            $record->dtid = $dtid;
            $record->helper = '\\Module\\' . ucfirst($record->module) . '\\Classes\\Listing\\Datatype_' . ucfirst($record->dtid) . 'Helper';
            
            unset ($record);
            
        endforeach;
        
        self::$cache[$lid] = $data;
        
    }
    
}