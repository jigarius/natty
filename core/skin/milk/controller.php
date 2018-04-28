<?php

namespace Skin\Milk;

class Controller
extends \Natty\Core\PackageObject {
    
    public function onSystemBeforeRender() {
        
        $response = \Natty::getResponse();
        $response->addMeta(array (
            '_key' => 'viewport',
            'name' => 'viewport',
            'content' => 'width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no',
        ));
        
    }
    
}