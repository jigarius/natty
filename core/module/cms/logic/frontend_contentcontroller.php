<?php

namespace Module\Cms\Logic;

class Frontend_ContentController {
    
    public static function pageView($content) {
        
        \Natty::getResponse()->attribute('title', $content->name);
        
        $output = $content->render(array (
            'page' => TRUE,
        ));
        
        return $output;
        
    }
    
}