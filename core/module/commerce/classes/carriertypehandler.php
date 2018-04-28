<?php

namespace Module\Commerce\Classes;

class CarriertypeHandler {
    
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
    
    public static function readOptions(array $options = array ()) {
        
        $data = self::read($options);
        $output = array ();
        
        foreach ( $data as $ctid => $ctype ):
            $output[$ctid] = array (
                'value' => $ctid,
                '_data' => $ctype->name,
            );
        endforeach;
        
        return $output;
        
    }
    
    public static function rebuild(array $options = array ()) {
        
        if ( !isset ($options['language']) )
            $options['language'] = \Natty::getOutputLangId ();
        $lid = $options['language'];
        
        // Trigger an event to collect data
        $data = array ();
        \Natty::trigger('commerce--carriertypeDeclare', $data);
        \Natty::trigger('commerce--carriertypeRevise', $data);
        
        foreach ( $data as $ctid => &$record ):
            
            $record = (object) $record;
            $record->ctid = $ctid;
            $record->helper = '\\Module\\' . ucfirst($record->module) . '\\Classes\\Commerce\\Carriertype_' . ucfirst($record->ctid) . 'Helper';
            
            unset ($record);
            
        endforeach;
        
        self::$cache[$lid] = $data;
        
    }
    
}