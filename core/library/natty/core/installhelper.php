<?php

namespace Natty\Core;

/**
 * Package Installer
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class InstallHelper {

    public static function enable( $type, $code ) {
        
        // Enable the package
        $pid = PackageObject::type2typecode($type) . '-' . $code;
        $package = \Natty::getEntity('system--package', $pid);
        
        if ( $package->status )
            return;
        
        $package->status = 1;
        $package->save();
        
        // Package specific actions
        $package_installer = $package->getInstallerClass();
        $package_installer::enable();
        
        // Trigger an event
        \Natty::getPackage('module', NULL, TRUE);
        \Natty::trigger('system--rebuildRegistry');
    
    }
    
    public static function disable( $type, $code ) {
        
        // Disable the package
        $pid = PackageObject::type2typecode($type) . '-' . $code;
        $package = \Natty::getEntity('system--package', $pid);
        
        if ( !$package || !$package->status )
            return;
        
        $package->status = 0;
        $package->save();

        // Package specific actions
        $package_installer = $package->getInstallerClass();
        $package_installer::disable();
        
        // Trigger an event
        \Natty::getPackage('module', NULL, TRUE);
        \Natty::trigger('system/rebuildRegistry');
        
    }
    
    public static function install( $type, $code ) {
        
        $package_handler = \Natty::getHandler('system--package');
        $definition = $package_handler::readFromDisk(array (
            'type' => $type,
            'code' => $code
        ));
        
        if ( !$definition )
            throw new \RuntimeException('Package "' . $type . '-' . $code . '" was not found.');
        
        $package = $package_handler->create($definition);
        $package->isNew = TRUE;
        $package_installer = $package->getInstallerClass();
        
        // Validate dependencies (if any)
        if ( $package->dependencies ):
            $installed_modules = \Natty::getPackage('module');
            foreach ( $package->dependencies as $dependency ):
                if ( !isset ($installed_modules[$dependency]) )
                    throw new \RuntimeException('Package "' . $type . '-' . $code . '" requires "' . $type . '-' . $dependency . '" to be installed first.');
            endforeach;
        endif;
        
        // Is the system module being installed?
        $is_mod_system = 'module' == $type && 'system' == $code;
        
        // Trigger install event first for system module
        // to prepare system tables
        if ( $is_mod_system )
            $package_installer::install();
        
        // Save package definition
        $package->save();
        
        // Module specific actions
        if ( !$is_mod_system )
            $package_installer::install();
        
        // Resolve dependencies
        self::resolveDependencies();
        
        // Trigger an event
        \Natty::trigger('system/packageInstall', $package);
        
        // Enable the package and trigger first-run
        \Natty::writeSetting($package->code . '--installing', 1);
        self::enable($type, $code);
        \Natty::writeSetting($package->code . '--installing', NULL);
        
    }
    
    public static function uninstall( $type, $code ) {
        
        // Disable the package
        $pid = \Natty\Core\PackageObject::type2typecode($type) . '-' . $code;
        $package_handler = \Natty::getHandler('system--package');
        $package = $package_handler->readById($pid);
        if ( !$package )
            return;
        self::disable($type, $code);
        
        $package_installer = $package->getInstallerClass();
        $package_installer::uninstall();
        
        $package->delete();
        
        // Trigger an event
        \Natty::trigger('system/packageUninstall', $package);
        
    }
    
    public static function resolveDependencies() {
        
        $package_handler = \Natty::getHandler('system--package');
        $packages = $package_handler->read(array (
            'conditions' => '{type} = :type AND {status} = :status',
            'parameters' => array ('type' => 'module', 'status' => 1),
            'ordering' => array ('isSystem' => 'desc', 'ooa' => 'asc'),
            'nocache' => TRUE
        ));
        
        // All packages must have an ooa
        $packge_index = 0;
        foreach ( $packages as $package ):
            if ( !$package->ooa )
                $package->ooa = $package->isSystem ? (++$package_index) : 999;
        endforeach;
        
        uasort($packages, function($item1, $item2) {
            
            $output = 0;
            
            // Deal with system modules
            if ( $item1->isSystem && !$item2->isSystem ):
                return -1;
            endif;
            if ( !$item1->isSystem && $item2->isSystem ):
                return 1;
            endif;
            if ( $item1->isSystem && $item2->isSystem ):
                return $item1->ooa - $item2->ooa;
            endif;
            
            // Deal with non-system modules
            if ( !$item1->dependencies && !$item2->dependencies )
                return 0;
            if ( in_array($item1->code, $item2->dependencies) )
                return -1;
            if ( in_array($item2->code, $item1->dependencies) )
                return 1;
            
            return $output;
            
        });
        
        // Save the new ordering data
        $ooa = 0;
        foreach ( $packages as $package ):
            $ooa += 5;
//            $ooa = $package->isSystem
//                ? $package->ooa : $ooa+5;
            $package->ooa = $ooa;
            $package->save();
        endforeach;
        
        // Clear package static cache
        $package_handler->staticCacheTruncate();
        
    }
    
    public static function refresh($type, $code) {
        
        $pid = \Natty\Core\PackageObject::type2typecode($type) . '-' . $code;
        $package_handler = \Natty::getHandler('system--package');
        $package = $package_handler->readById($pid);
        $defi = $package_handler::readFromDisk(array (
            'type' => $type,
            'code' => $code
        ));
        
        // Update information and save
        $package->setState($defi);
        $package->save();
        
        // Rebuild stuff
        \Natty::trigger('system/rebuildRegistry');
        
    }
    
}