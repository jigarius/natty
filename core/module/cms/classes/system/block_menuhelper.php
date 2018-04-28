<?php

namespace Module\Cms\Classes\System;

use \Module\System\Classes\BlockHelperAbstract;

class Block_MenuHelper
extends BlockHelperAbstract {
    
    public static function handleSettingsForm(&$data) {
        
        $form =& $data['form'];
        $entity =& $data['entity'];
        $fcont_default =& $form->items['default'];
        $settings = self::getDefaultSettings() + $entity->settings;
        
        switch ( $form->getStatus() ):
            case 'prepare':

                $menu_opts = \Natty::getHandler('cms--menu')->readOptions(array (
                    'ordering' => array ('name' => 'asc'),
                    'valueProperty' => 'mcode',
                ));
                
                $fcont_default['_data']['settings.mcode'] = array (
                    '_widget' => 'dropdown',
                    '_label' => 'Menu',
                    '_options' => $menu_opts,
                    '_default' => $settings['mcode'],
                    'placeholder' => '',
                    'required' => 1,
                );
                
                break;
        endswitch;
        
    }
    
    public static function buildOutput(array $settings) {
        
        $output = array ();
        
        // Read items from the said menu
        $menuitem_coll = \Natty::getHandler('cms--menuitem')
            ->read(array (
                'key' => array ('mcode' => $settings['mcode']),
                'ordering' => array (
                    'parentId' => 'asc',
                    'ooa' => 'asc',
                ),
            ));
        
        $output['_data'] = \Module\Cms\Classes\MenuHandler::buildMenuTree($menuitem_coll);
        $output['class'][] = $settings['mcode'];
        
        return $output;
        
    }
    
    public static function getDefaultSettings() {
        $output = parent::getDefaultSettings();
        $output['mcode'] = NULL;
        return $output;
    }
    
}