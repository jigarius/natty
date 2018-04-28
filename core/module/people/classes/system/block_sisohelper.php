<?php

namespace Module\People\Classes\System;

class Block_SisoHelper
extends \Module\System\Classes\BlockHelperAbstract {
    
    public static function buildOutput(array $settings) {
        
        $command = \Natty::getCommand();
        $user = \Natty::getUser();
        $output = array (
            '_data' => array (),
        );

        // User is signed in?
        if ( $user->uid ) {
            if ( $command != 'dashboard' && $user->can('system--view dashboard') )
                $output['_data']['dashboard'] = '<a href="' . \Natty::url('dashboard') . '" class="dashboard">Dashboard</a> ';
            if ( $user->can('people--edit own profile') )
                $output['_data']['profile'] = '<a href="' . \Natty::url('user/edit-profile') . '" class="name">' . $user->getVar('name', 'Anonymous') . '</a> ';
            else
                $output['_data']['profile'] = '<a class="fullname">' . $user->getVar('name', 'Anonymous') . '</a> ';
            $output['_data']['signout'] = '<a href="' . \Natty::url('sign-out') . '" class="sign-out">Sign out</a> ';
        }
        // User is not signed in!
        else {
            $output['_data']['sign-in'] = '<a href="' . \Natty::url('sign-in') . '" class="sign-in">Sign in</a> ';
        }
        
        return $output;
        
    }
    
}