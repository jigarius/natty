<?php

namespace Module\Easex\Logic;

class DefaultController {
    
    public static function pageDashMenu() {
        
        // Load dependencies
        $user = \Natty::getUser();
        $route_handler = \Natty::getHandler('system--route');

        // Can the user see the dashmenu?
        if ( !$user->can('easex--view toolbar') )
            die;

        // Prepare toolbar
        $toolbar = '<div id="easex-toolbar">'
                        . '<div class="logo-unit">'
                            . '<a href="' . \Natty::url('dashboard') . '" class="dashmenu-trigger">'
                                . '<span class="icon fa fa-bars"></span> <span class="text">' . \Natty::readSetting('system--siteName') . '</span>'
                            . '</a>'
                        . '</div>'
                        . '<div class="user-unit">'
                            . '<a href="' . \Natty::url('dashboard/your-account') . '" class="username">'
                                . '<span class="icon fa fa-user"></span> <span class="text">' . $user->name . '</span>'
                            . '</a>';
        if ( $user->uid ) {
            $toolbar .= '<a href="' . \Natty::url('sign-out') . '" class="sign-out">'
                . '<span class="icon fa fa-sign-out"></span> <span class="text">Sign out</span>'
            . '</a>';
        }
        else {
            $toolbar .= '<a href="' . \Natty::url('sign-in') . '" class="sign-in">'
                . '<span class="icon fa fa-sign-in"></span> <span class="text">Sign in</span>'
            . '</a>';
        }
        $toolbar .= '</div>'
                    . '</div>';

        // Prepare dashmenu
        $list_data = $route_handler->read(array (
            'conditions' => array (
                array ('AND', array ('parentId', 'LIKE', ':parentId')),
                array ('AND', array ('variables', '=', ':variables')),
            ),
            'parameters' => array (
                'parentId' => 'dashboard%',
                'variables' => 0,
            ),
            'ordering' => array (
                'parentId' => 'asc',
                'ooa' => 'asc',
                'heading' => 'asc',
            ),
        ));

        function easex_prepare_dashmenu($data, $rid = 'dashboard') {

            $output = array ();

            // Add children
            foreach ( $data as $link ):

                if ( $rid != $link->parentId )
                    continue;

                // Add link
                $link_data = array (
                    '<a href="' . \Natty::url($link->rid) . '">' . $link->heading . '</a>',
                );

                // Add children
                $children = easex_prepare_dashmenu($data, $link->rid);
                if ( sizeof($children) ):
                    if ( !isset ($link_data[1]) ):
                        $link_data[1] = array (
                            '_render' => 'list',
                            '_items' => array (),
                            '_item_class' => FALSE,
                            'class' => array (),
                        );
                    endif;
                    $link_data[1]['_items'] = array_merge($link_data[1]['_items'], $children);
                endif;

                $output[] = array (
                    '_data' => $link_data,
                );

            endforeach;

            return $output;

        }

        // Add other useful links
        $common_tasks = array (
            '_data' => array (
                '<a href="#">Shortcuts</a>',
                array (
                    '_render' => 'list',
                    '_items' => array (
                        array (
                            '_render' => 'element',
                            '_element' => 'a',
                            '_data' => 'Home page',
                            'target' => '_blank',
                            'href' => NATTY_BASE,
                        ),
                        array (
                            '_render' => 'element',
                            '_element' => 'a',
                            '_data' => 'Dashboard',
                            'href' => \Natty::url('dashboard'),
                        ),
                    ),
                    'class' => array (),
                ),
            ),
        );
        
        $dashmenu_items = easex_prepare_dashmenu($list_data);
        $dashmenu_items[] = $common_tasks;
        $dashmenu = array (
            '<div id="easex-drawer">'
                // The dashmenu
                . '<div class="easex-dashmenu">',
                array (
                    '_render' => 'list',
                    '_items' => $dashmenu_items,
                    '_item_class' => FALSE,
                    'class' => array (),
                ),
                '</div>'
            . '</div>'
        );

        // Prepare response
        $output = array (
            $toolbar,
            $dashmenu,
        );

        // Render response
        echo natty_render($output);
        exit;
        
    }
    
}