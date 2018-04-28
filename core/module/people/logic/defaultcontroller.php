<?php

namespace Module\People\Logic;

class DefaultController {
    
    public static function permSignUp() {
        
        $auth_user = \Natty::getUser();
        if ( $auth_user->uid > 0 )
            return FALSE;
        
        return (bool) \Natty::readSetting('people--userRegiEnabled');
        
    }
    
}