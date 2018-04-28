<?php

namespace Module\Example\Logic;

class Dbal_QueryController {
    
    public static function pageCustom() {
        
        $dbo = \Natty\Database::getInstance();

        $query = 
            "SELECT *
            FROM {%__core_user} {u}
            WHERE
                {u}.{status} != :status_active
                AND {u}.{id} NOT IN (
                    SELECT {id} 
                    FROM {%__user} {u}
                    WHERE {u}.{roleId} = :admin
                )
            ORDER BY {u}.{name}, {u}.{email}";
//        $dbo->execute($query);
        
        natty_debug();
        
    }
    
    public static function pageCreate() {
        
        $dbo = Natty::getDbo();

        natty_debug();
        
    }
    
    public static function pageRead() {
        
        $dbo = Natty::getDbo();
        
        natty_debug();
        
    }
    
    public static function pageUpdate() {
        
        $dbo = Natty::getDbo();
        
        natty_debug();
        
    }
    
    public static function pageDelete() {
        
        $dbo = Natty::getDbo();
        
        natty_debug();
        
    }
    
}