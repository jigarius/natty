<?php

namespace Module\System\Classes;

class SerialHelper {
    
    public static function generate($sequence, $delta = 'default') {
        
        if ( is_array($delta) )
            $delta = implode(':', $delta);
        
        $connection = \Natty::getDbo();
        $tablename = '%__system_serial';
        
        $record = $connection
                ->getQuery('select', $tablename . ' ss')
                ->addColumn('*', 'ss')
                ->addComplexCondition('AND', array ('sequence', '=', ':sequence'))
                ->addComplexCondition('AND', array ('delta', '=', ':delta'))
                ->execute(array (
                    'sequence' => $sequence,
                    'delta' => $delta,
                ))
                ->fetch();
        
        if ( !$record ) {
            $record = array (
                'sequence' => $sequence,
                'delta' => $delta,
                'count' => 1,
            );
            $connection->insert($tablename, $record);
        }
        else {
            $record['count'] += 1;
            $connection->update($tablename, $record, array (
                'keys' => array ('sequence', 'delta')
            ));
        }
        
        return $record['count'];
        
    }
    
    public static function delete($sequence, $delta = '*') {
        
        $lookup_keys = array ('sequence' => $sequence);
        if ( '*' != $delta )
            $lookup_keys['delta'] = $delta;
        
        $connection = \Natty::getDbo();
        $connection->delete('%__system_serial', array (
            'key' => $lookup_keys,
        ));
        
    }
    
}