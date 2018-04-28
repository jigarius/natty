<?php

namespace Module\Example;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemEntityTypeDeclare(&$data) {
        include 'declare/system-entitytype.php';
    }
    
    public static function onSystemEmailDeclare(&$data) {
        $data['example--advanced email'] = array (
            'name' => 'Advanced email example',
            'subject' => 'Advanced email example',
        );
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    /**
     * This is a custom callback and not a standard form event listener.
     * @param array $data
     */
    public static function validateExampleFormStandard(array $data) {
        
        $form = $data['form'];
        if ( 'validate' === $form->getStatus() ):
            \Natty\Console::debug('Custom form validation callback was called.');
        endif;
        
    }
    
}