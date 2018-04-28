<?php

namespace Module\Media;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onEavDatatypeDeclare(&$data) {
        include 'declare/eav-datatype.php';
    }
    
    public static function onMediaImageprocDeclare(&$data) {
        include 'declare/media-imageproc.php';
    }
    
    public static function onEavInputmethodDeclare(&$data) {
        $data['media--upload'] = array (
            'module' => 'media',
            'name' => 'Standard Uploader',
            'datatypes'=> array ('media--file', 'media--image'),
            'isMultiValue' => 1,
        );
    }
    
    public static function onEavOutputmethodDeclare(&$data) {
        $data['media--filename'] = array (
            'module' => 'media',
            'name' => 'Filename',
            'datatypes' => array ('media--file', 'media--image'),
        );
        $data['media--image'] = array (
            'module' => 'media',
            'name' => 'Image',
            'datatypes' => array ('media--image'),
        );
    }
    
    public static function readImageProc(array $options = array ()) {
        
        static $cache;
        
        if ( is_null($cache) || !isset ($options['nocache']) ):
            
            $cache = array ();
            
            \Natty::trigger('media--imageprocDeclare', $cache);
            \Natty::trigger('media--imageprocRevise', $cache);
            
            foreach ( $cache as $ipid => &$record ):
                
                $ipid_parts = explode('--', $ipid);
                $record['helper'] = '\\Module\\' . ucfirst($ipid_parts[0]) . '\\Classes\\Media\\Imageproc_' . ucfirst($ipid_parts[1]) . 'Helper';
                
                unset ($record);
                
            endforeach;
            
        endif;
        
        if ( isset ($options['ipid']) ):
            $ipid = $options['ipid'];
            return isset ($cache[$ipid])
                ? $cache[$ipid] : FALSE;
        endif;
        
        return $cache;
        
    }
    
}