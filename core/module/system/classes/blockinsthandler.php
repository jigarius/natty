<?php

namespace Module\System\Classes;

class BlockinstHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    /**
     * Static cache for helper classnames
     * @var array
     */
    protected static $helpers = array ();
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'tableName' => '%__system_blockinst',
            'etid' => 'system--blockinst',
            'modelName' => array ('block instance', 'block instances'),
            'keys' => array (
                'id' => 'biid',
                'label' => 'description',
            ),
            'properties' => array (
                'biid' => array (),
                'bid' => array (),
                'description' => array (),
                'heading' => array ('default' => NULL),
                'visibility' => array ('default' => array (), 'serialized' => TRUE),
                'status' => array ('default' => 1),
                'statusExceptions' => array ('default' => array (), 'sdata' => TRUE),
                'settings' => array ('default' => array (
                    'cssClass' => '',
                ), 'sdata' => TRUE),
                'isLocked' => array ('default' => 0),
            )
        );
        
        parent::__construct($options);
        
    }
    
    public static function getHelperClass($bid) {
        if ( !isset (self::$helpers[$bid]) ):
            $bid_parts = explode('--', $bid, 2);
            self::$helpers[$bid] = '\\Module\\' . ucfirst($bid_parts[0]) . '\\Classes\\System\\Block_' . ucfirst($bid_parts[1]) . 'Helper';
        endif;
        return self::$helpers[$bid];
        
    }
    
    public function onBeforeSave(&$entity, array $options = array ()) {
        
        // Create settings for all available skins
        $skin_coll = \Natty::getHandler('system--package')->read(array (
            'key' => array ('type' => 'skin', 'status' => 1),
        ));
        
        foreach ( $skin_coll as $skin ):
            
            $skin_code = $skin->code;
            
            if ( !isset ($entity->visibility[$skin_code]) )
                $entity->visibility[$skin_code] = array ();
            
            $entity->visibility[$skin_code] = array_merge(array (
                'position' => NULL,
                'ooa' => NULL,
            ), $entity->visibility[$skin_code]);
            
            if ( !$entity->visibility[$skin_code]['ooa'] )
                $entity->visibility[$skin_code]['ooa'] = 0;
            
        endforeach;
        
        // Delete data for skins which do not exist
        foreach ( $entity->visibility as $skin_code => $data ):
            if ( !isset ($skin_coll['ski-' . $skin_code]) )
                unset ($entity->visibility[$skin_code]);
        endforeach;
        
        // Merge with default settings
        $block_coll = \Module\System\Controller::readBlocks();
        $block = $block_coll[$entity->bid];
        $default_settings = $block['helper']::getDefaultSettings();
        $entity->settings = array_merge($default_settings, $entity->settings);
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    protected function onBeforeDelete(&$entity, array $options = array ()) {
        
        $helper = self::getHelperClass($entity->bid);
        return $helper::onBeforeDelete($entity, $options);
        
        parent::onBeforeDelete($entity, $options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array ()) {
        
        // Determine skin code
        if ( !isset ($options['skinCode']) )
            $options['skinCode'] = \Natty::getSkin()->code;
        $skin_code = $options['skinCode'];
        
        $output = array ();
        
        // Configuration link
        $output['configure'] = '<a href="' . \Natty::url('backend/system/block-inst/' . $skin_code . '/' . $entity->biid) . '">Configure</a>';
        
        $output['delete'] = '<a href="' . \Natty::url('backend/system/block-inst/action', array (
            'do' => 'delete', 'with' => $entity->biid,
        )) . '" data-ui-init="confirmation">Delete</a>';
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    public function getRarray(&$entity, array $options = array ()) {
        
        // Determine the skin
        if ( !isset ($options['skin_code']) )
            $options['skin_code'] = \Natty::getSkin()->code;
        $skin_code = $options['skin_code'];
        
        // See if we are on the home page
        $command = \Natty::getCommand();
        $is_home = \Natty::readSetting('system--routeDefault') === $command;
        
        // Determine the skin-specific configuration
        $config = $entity->visibility[$skin_code];
        if ( !$config['position'] )
            return;
        
        // Determine status and exceptions
        if ( $entity->statusExceptions ):
            
            // See if the command matches an exception
            $exception_matched = FALSE;
            foreach ( $entity->statusExceptions as $pattern ):
                
                if ( 'home' === $pattern && $is_home ):
                    $exception_matched = TRUE;
                    break;
                endif;
                
                $pattern = '#' . str_replace('%', '[\w]+', $pattern) . '#';
                if ( preg_match($pattern, $command) ):
                    $exception_matched = TRUE;
                    break;
                endif;
                
            endforeach;
            
            // Determine action based on default
            if ( $exception_matched && $entity->status )
                return NULL;
            if ( !$exception_matched && !$entity->status )
                return NULL;
            
        endif;
        
        // Get the block provider
        $callback_classname = self::getHelperClass($entity->bid);
        $callback_method = 'buildOutput';
        
        // Invoke the provider to render the block
        $settings = $entity->settings;
        $settings['biid'] = $entity->biid;
        $rarray = $callback_classname::$callback_method($settings);
        
        // Merge output with defaults
        $rarray = array_merge(array (
            '_block' => $entity->bid,
            '_data' => NULL,
            '_ooa' => $entity->visibility[$skin_code]['ooa'],
            'class' => array (),
        ), $rarray);
        
        $rarray['_heading'] = $entity->heading;
        
        // Add block instance ID
        if ( isset ($entity->biid) )
            $rarray['class'][] = 'blockinst-' . $entity->biid;
        
        // Add css class specified in settings
        if ( strlen($entity->settings['cssClass']) > 0 )
            $rarray['class'][] = $entity->settings['cssClass'];
        
        // Trigger an event
        $event_data = array (
            'entity' => $entity,
            'rarray' => &$rarray,
        );
        \Natty::trigger('system--blockinstView', $event_data);
        
        return $rarray;
        
    }
    
}