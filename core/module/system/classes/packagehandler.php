<?php

namespace Module\System\Classes;

class PackageHandler
extends \Natty\ORM\BasicDatabaseEntityHandler {
    
    public function __construct( array $options = array () ) {
        
        $options = array (
            'tableName' => '%__system_package',
            'etid' => 'system--package',
            'singularName' => 'package',
            'pluralName' => 'packages',
            'keys' => array (
                'id' => 'pid',
            ),
            'entityObjectClass' => '\\Natty\\Core\\PackageObject',
            'properties' => array (
                'pid' => array (),
                'type' => array (),
                'code' => array (),
                'version' => array (),
                'name' => array (),
                'description' => array (),
                'dependencies' => array ('sdata' => 1, 'default' => array ()),
                'stylesheets' => array ('sdata' => 1, 'default' => array ()),
                'scripts' => array ('sdata' => 1, 'default' => array ()),
                'positions' => array ('sdata' => 1, 'default' => array ()),
                'path' => array (),
                'tsCreated' => array (),
                'ooa' => array ('default' => 999),
                'isSystem' => array (),
                'status' => array ()
            )
        );
        
        parent::__construct($options);
        
    }
    
    public function create(array $data = array ()) {
        
        // Validate type and code
        if ( !isset ($data['type']) || !isset ($data['code']) )
            throw new \InvalidArgumentException('Data must have indices "type" and "code"');
        
        // Add timestamp of creation
        if ( !isset ($data['tsCreated']) )
            $data['tsCreated'] = time();
        
        // Register handler
        $data['handler'] =& $this;
        
        // Return object of the required type
        $classname = ucfirst($data['type']) . '\\' . natty_strtocamel($data['code'], TRUE) . '\\Controller';
        if ( !class_exists($classname) )
            $classname = $this->entityObjectClass;
        
        $defaults = $this->getEntityDefaultState();
        
        $data = natty_array_merge_nested($defaults, $data);
        
        return new $classname($data);
        
    }
    
    public function validate($entity, array $options = array()) {
        
        if ( !in_array($entity->type, array ('skin', 'module')) )
            throw new \Natty\ORM\EntityException('Invalid value for required property "type"');
        
        if ( !$entity->code )
            throw new \Natty\ORM\EntityException('Invalid value for required property "code"');
        
        if ( !$entity->pid )
            throw new \Natty\ORM\EntityException('Invalid value for required property "pid"');
        
        parent::validate($entity, $options);
        
    }
    
    protected function onBeforeSave(&$entity, array $options = array()) {
        
        // This code should be in the system module
        if ( 'skin' === $entity->type ):
            
            $entity->positions['content'] = 'Content';
            $entity->positions['hidden'] = 'Hidden';
            
        endif;
        
        parent::onBeforeSave($entity, $options);
        
    }
    
    public static function readFromDisk(array $options = array ()) {
        
        // Determine options
        $options = array_merge(array (
            'type' => FALSE,
            'code' => '*',
            'paths' => FALSE,
        ), $options);
        
        // Determine type
        if ( !$options['type'] )
            throw new \InvalidArgumentException('Invalid value for index "type".');
        
        // Determine lookup paths
        if ( !$options['paths'] ):
            $options['paths'] = array ();
            $options['paths'][] = 'core/' . $options['type'] . DS . $options['code'] . '/package.yml';
            $options['paths'][] = 'common/' . $options['type'] . DS . $options['code'] . '/package.yml';
            $options['paths'][] = \Natty::readSetting('system--sitePath') . DS . $options['type'] . DS . $options['code'] . '/package.yml';
        endif;
        
        // Perform the lookup
        $output = array ();
        foreach ( $options['paths'] as $t_path ):
            $results = glob(NATTY_ROOT . DS . $t_path);
            foreach ( $results as &$t_result ):
                
                // Load definition
                if ( !$item = \Spyc::YAMLLoad($t_result) )
                    continue;
                
                // Merge with defaults
                $item = array_merge(array (
                    'version' => '1.0',
                    'stylesheets' => array (),
                    'scripts' => array (),
                    'isSystem' => 0,
                ), $item);
                
                // Determine additional data
                $item['path'] = pathinfo($t_result, PATHINFO_DIRNAME);
                $item['path'] = str_replace(NATTY_ROOT . DS, '', $item['path']);
                $item['type'] = $options['type'];
                $item['code'] = basename($item['path']);
                $item['pid'] = \Natty\Core\PackageObject::type2typecode($options['type']) . '-' . $item['code'];
                
                // If a module with the same name has already been found
                if ( isset ($output[$item['pid']]) )
                    continue;
                
                $output[$item['pid']] = $item;
                
                unset ($item);
                
            endforeach;
        endforeach;
        
        // Return a unique package?
        if ( '*' != $options['code'] ):
            if ( 1 != sizeof($output) )
                return FALSE;
            return array_shift($output);
        endif;
        
        return $output;
        
    }
    
    public static function typecode2type($typecode) {
        $output = FALSE;
        switch ( $typecode ):
            case 'mod':
                $output = 'module';
                break;
            case 'ski':
                $output = 'skin';
                break;
        endswitch;
        return $output;
    }
    
    public static function type2typecode($type) {
        return substr($type, 0, 3);
    }
    
    public function buildBackendLinks(&$entity, array $options = array ()) {
        
        $output = array ();
        
        return $output;
        
    }
    
}