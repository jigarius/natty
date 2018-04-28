<?php

namespace Module\System\Logic;

class EntityController {
    
    public static function routeHeadingCallback($entity) {
        return $entity->getLabel();
    }
    
    public static function routePermCallback($entity, $action) {
        return $entity->getHandler()->allowAction($entity, $action);
    }
    
    public static function routeContentCallback($entity, $view_mode = 'default') {
        $output = $entity->render(array (
            'viewMode' => $view_mode,
            'page' => TRUE,
        ));
        return $output;
    }
    
}