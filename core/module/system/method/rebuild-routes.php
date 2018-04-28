<?php

defined('NATTY') or die;

$route_handler = \Natty::getHandler('system--route');

// Delete existing routes
$route_coll = $route_handler->read(array (
    'condition' => '1=1'
));
foreach ( $route_coll as $route ):
    $route->delete();
endforeach;

// Read current declarations
$route_coll = array ();
\Natty::trigger('system/routeDeclare', $route_coll);
\Natty::trigger('system/routeRevise', $route_coll);

// Create new models
foreach ( $route_coll as $rid => $route ):

    $route['rid'] = $rid;
    $route['isNew'] = TRUE;
    $route = $route_handler->createAndSave($route);
    
    $route_coll[$rid] = $route;

    unset ($route);

endforeach;

// Set output to return
$output = $route_coll;