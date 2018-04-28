<?php

namespace Module\System\Logic;

class Backend_PackageController {
    
    public static function pageManage($package_type) {
        
        $mod_system = \Natty::getPackage('module', 'system');
        $skin_fe = \Natty::readSetting('system--frontendSkin', NATTY_SKIN_DEFAULT);
        $skin_be = \Natty::readSetting('system--backendSkin', NATTY_SKIN_DEFAULT);

        $package_handler = \Natty::getHandler('system--package');
        $package_coll = $package_handler->read(array (
            'key' => array ('type' => $package_type),
            'ordering' => array (
                'isSystem' => 'desc',
                'name' => 'asc',
            )
        ));

        // List head
        $list_head = array (
            array ('_data' => '', 'class' => array ('cont-checkbox')),
            array ('_data' => '', 'class' => array ('cont-image'), '_display' => ('skin' == $package_type)),
            array ('_data' => 'Package Name'),
            array ('_data' => 'Version', 'width' => 80),
            array ('_data' => '', 'class' => array ('context-menu'))
        );

        // List body
        $list_body = array ();
        foreach ( $package_coll as $package ):

            // Determine package preview
            $package_preview = NATTY_ROOT . DS . $package->path . '/thumbnail.png';
            if ( !is_file($package_preview) )
                $package_preview = NATTY_ROOT . DS . $mod_system->path . '/reso/no-image.jpg';
            $package_preview = str_replace(NATTY_ROOT . '/', NATTY_BASE, $package_preview);

            $row = array ();

            $row[]= '<input type="checkbox" ' . ($package->status ? 'checked="checked"' : '') . ' name="items[' . $package->code . ']" disabled="disabled" value="1" />';

            $row[] = array (
                '_display' => 'skin' == $package_type,
                '_data' => '<a href="' . $package_preview . '" target="_blank"><img src="' . $package_preview . '" class="prop-image" /></a>'
            );

            $package_suffix = '';
            if ( 'skin' === $package->type ):
                if ( $skin_fe == $package->code )
                    $package_suffix .= ' (Frontend default)';
                if ( $skin_be == $package->code )
                    $package_suffix .= ' (Backend default)';
            endif;
            
            $row[]= '<div class="prop-title">' . $package->name . $package_suffix . '</div>'
                    . '<div class="prop-description">' . $package->description . '</div>';

            $row[]= $package->version;

            $row['context-menu'] = array ();

            if ( $package->status ) {

                if ( 'skin' === $package_type ):
                    if ( $package->code != $skin_fe ):
                        $row['context-menu']['fe-default'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                            'do' => 'fe-default', 'with' => $package->pid,
                        )) . '">Front end default</a>';
                    endif;
                    if ( $package->code != $skin_be ):
                        $row['context-menu']['be-default'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                            'do' => 'be-default', 'with' => $package->pid,
                        )) . '">Back end default</a>';
                    endif;
                endif;

                if ( !$package->isSystem || 1 == \Natty::getUser()->uid ):

                    $row['context-menu']['disable'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                        'do' => 'disable', 'with' => $package->pid,
                    )) . '">Disable</a>';

                    $row['context-menu']['reinstall'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                        'do' => 'reinstall', 'with' => $package->pid,
                    )) . '" data-ui-init="confirmation">Reinstall</a>';

                    $row['context-menu']['uninstall'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                        'do' => 'uninstall', 'with' => $package->pid,
                    )) . '" data-ui-init="confirmation">Uninstall</a>';

                endif;

                $row['context-menu']['refresh'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                    'do' => 'refresh', 'with' => $package->pid,
                )) . '">Refresh</a>';

            }
            else {
                $row['context-menu']['enable'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                    'do' => 'enable', 'with' => $package->pid,
                )) . '">Enable</a>';
            }

            $list_body[] = $row;

        endforeach;

        // Prepare output
        $output[] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/system/' . $package_type . '/install') . '" class="k-button">Install</a>',
        ));
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
    public static function pageAction() {
        
        $request = \Natty::getRequest();
        $package_handler = \Natty::getHandler('system--package');

        if ( !$with = $request->getVar('with') )
            \Natty::error(500);
        list ($package_type, $package_code) = explode('-', $with);

        $package_type = $package_handler::typecode2type($package_type);

        try {
            switch ( $request->getVar('do') ):
                case 'install':
                    \Natty\Core\InstallHelper::install($package_type, $package_code);
                    break;
                case 'uninstall':
                    \Natty\Core\InstallHelper::uninstall($package_type, $package_code);
                    break;
                case 'reinstall':
                    \Natty\Core\InstallHelper::uninstall($package_type, $package_code);
                    \Natty\Core\InstallHelper::install($package_type, $package_code);
                    break;
                case 'refresh':
                    \Natty\Core\InstallHelper::refresh($package_type, $package_code);
                    break;
                case 'enable':
                    \Natty\Core\InstallHelper::enable($package_type, $package_code);
                    break;
                case 'disable':
                    \Natty\Core\InstallHelper::disable($package_type, $package_code);
                    break;
                case 'fe-default':
                    if ( $package_type != 'skin' )
                        \Natty::error(400);
                    if ( !$package = $package_handler->readById($with) )
                        \Natty::error(400);
                    \Natty::writeSetting('system--frontendSkin', $package->code);
                    break;
                case 'be-default':
                    if ( $package_type != 'skin' )
                        \Natty::error(400);
                    if ( !$package = $package_handler->readById($with) )
                        \Natty::error(400);
                    \Natty::writeSetting('system--backendSkin', $package->code);
                    break;
                default:
                    \Natty::error(400);
            endswitch;
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
        }
        catch ( Exception $ex ) {
            \Natty\Console::error('Error: ' . $ex->getMessage());
        }

        // Bounce back
        $location = \Natty::url('backend/system/' . $package_type);
        \Natty::getResponse()->bounce($location);
        
    }
    
    public static function pageInstall($package_type) {
        
        // Read packages from disk
        $package_handler = \Natty::getHandler('system--package');
        $disk_packages = $package_handler::readFromDisk(array (
            'type' => $package_type
        ));

        // Ignore packages which are already installed
        $installed_pids = \Natty::getDbo()
                ->getQuery('select', '%__system_package')
                ->addColumn('pid')
                ->addComplexCondition('AND', '{type} = :type')
                ->execute(array (
                    'type' => $package_type
                ))
                ->fetchAll(\PDO::FETCH_COLUMN);
        $installed_pids = array_flip($installed_pids);
        $disk_packages = array_diff_key($disk_packages, $installed_pids);

        // Prepare list
        $list_head = array (
            array ('_data' => 'Package'),
            array ('_data' => 'Version', 'width' => 100),
            array ('_data' => '', 'class' => array ('context-menu'))
        );
        $list_body = array ();
        foreach ( $disk_packages as $package ):

            $package = (object) $package;

            $row = array ();
            $row[]= '<div class="prop-title">' . $package->name . '</div>'
                    . '<div class="prop-description">' . $package->description . '</div>';
            $row[] = $package->version;

            $row['action'] = '<a href="' . \Natty::url('backend/system/package/action', array (
                'do' => 'install',
                'with' => $package->pid,
                'bounce' => TRUE,
            )) . '" class="k-button">Install</a>';

            $list_body[] = $row;

        endforeach;

        // Prepare output
        $output = array ();
        
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        
        return $output;
        
    }
    
}