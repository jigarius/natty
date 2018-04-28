<?php

namespace Module\System\Logic;

class DashboardController {

    public static function pageDashboard() {
        
        // Determine root directory
        $command = \Natty::getCommand();

        $route_handler = \Natty::getHandler('system--route');
        $route_coll = $route_handler->read(array(
            'conditions' => array(
                array('AND', array('variables', '=', 0)),
                array('AND', array('parentId', '=', ':parentId')),
            ),
            'parameters' => array(
                'parentId' => $command,
            ),
            'ordering' => array(
                'ooa' => 'asc',
                'heading' => 'asc',
            ),
        ));
        
        // Render output
        $output = array();
        $output[] = '<div class="system-dashmenu-cont">';
        $output['dashmenu'] = array (
            '_render' => 'list',
            '_items' => array(),
            'class' => array('system-dashmenu'),
        );
        foreach ($route_coll as $rid => $route):

            // Item without heading?
            if ( !$route->heading )
                continue;
            
            if ($route->execute('perm', $rid)):
                $item_classname = 'item-' . str_replace('/', '-', $rid);
                $item_markup =
                    '<a href="' . \Natty::url($route->rid) . '" class="n-icon n-icon-' . $route->icon . '"></a>'
                    . '<a href="' . \Natty::url($route->rid) . '" class="text">' . $route->heading . '</a>';
                if ($route->description)
                    $item_markup .= '<div class="description">' . $route->description . '</div>';
                $output['dashmenu']['_items'][] = array (
                    'class' => array ($item_classname),
                    '_data' => $item_markup,
                );
            endif;

        endforeach;
        $output[] = '</div">';
        
        
        return $output;
        
    }

}
