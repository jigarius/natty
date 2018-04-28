<?php

namespace Module\System\Logic;

class Backend_IncidentController {
    
    public static function pageManage() {
        
        return 'List of incidents.';
        
    }
    
    public static function pageView($incident) {
        
        return 'Render incident details.';
        
    }
    
}