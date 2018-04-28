<?php

namespace Module\Eav;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemActionDeclare(&$data) {
        include 'declare/system-action.php';
    }
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onSystemCron($data) {
        
        $attrinst_handler = \Natty::getHandler('eav--attrinst');
        
        // Read deleted attribute instances
        $attrinst_coll = $attrinst_handler->read(array (
            'key' => array (
                'status' => -1,
            ),
        ));
        foreach ( $attrinst_coll as $attrinst ):
            $crud_helper = $attrinst->crudHelper;
            try {
                $attrinst->delete();
            }
            catch ( \Exception $e ) {
                \Natty\Console::debug('Attribute instance ' . $attrinst->aiid . ' (' . $attrinst->acode . '): ' . $e->getMessage());
            }
        endforeach;
        
    }
    
    public static function readInputMethods($rebuild = FALSE) {
        
        static $output;
        
        if ( !is_array($output) || $rebuild ):
            
            $output = array ();
            \Natty::trigger('eav--inputMethodDeclare', $output);
            \Natty::trigger('eav--inputMethodRevise', $output);
            
            // Add defaults
            foreach ( $output as $imid => &$input_method ):
                $imid_parts = explode('--', $imid);
                $input_method = array_merge(array (
                    'helper' => '\\Module\\' . ucfirst($imid_parts[0]) . '\\Classes\\Eav\\InputMethod_' . ucfirst($imid_parts[1] . 'Helper'),
                    'isMultiValue' => 0,
                ), $input_method);
                unset ($input_method);
            endforeach;
            
        endif;
        
        return $output;
        
    }
    
    public static function readOutputMethods($rebuild = FALSE) {
        
        static $output;
        
        if ( !is_array($output) || $rebuild ):
            
            $output = array ();
            
            \Natty::trigger('eav/outputMethodDeclare', $output);
            \Natty::trigger('eav/outputMethodRevise', $output);
            
            // Touch output methods
            foreach ( $output as $omid => &$output_method ):
                $omid_parts = explode('--', $omid);
                $output_method['helper'] = '\\Module\\' . ucfirst($omid_parts[0]) . '\\Classes\\Eav\\OutputMethod_' . ucfirst($omid_parts[1] . 'Helper');
                unset ($output_method);
            endforeach;
            
        endif;
        
        return $output;
        
    }
    
    public static function readDataTypes($rebuild = FALSE) {
        
        $dtype_handler = \Natty::getHandler('eav--datatype');
        
        // Read existing declarations
        $existing_dtypes = $dtype_handler->read(array (
            'condition' => '1=1'
        ));
        
        if ( $rebuild ):
            
            $fresh_dtypes = array ();
            \Natty::trigger('eav/datatypeDeclare', $fresh_dtypes);
            \Natty::trigger('eav/datatypeRevise', $fresh_dtypes);
            
            // Update existing models
            foreach ( $existing_dtypes as $dtype ):

                $dtid = $dtype->dtid;

                // Entity-type was deleted?
                if ( !isset ($fresh_dtypes[$dtid]) ):
                    $dtype->delete();
                    unset ($existing_dtypes[$dtid]);
                    continue;
                endif;

                // Entity-type was updated!
                $record = $fresh_dtypes[$dtid];
                $dtype->setState($record);
                $dtype->save();

                unset ($fresh_dtypes[$dtid]);

            endforeach;

            // Insert new models
            foreach ( $fresh_dtypes as $dtid => $record ):
                $record['dtid'] = $dtid;
                $dtype = $dtype_handler->create($record);
                $dtype->isNew = TRUE;
                $dtype->save();
                $existing_dtypes[$dtid] = $dtype;
            endforeach;
            
        endif;
        
        $output = $existing_dtypes;
        
        return $output;
        
    }
    
    public static function onSystemRebuild() {
        self::readDataTypes(TRUE);
    }
    
    public static function onEavDatatypeDeclare(&$data) {
        include 'declare/eav-datatype.php';
    }
    
    public static function onEavInputmethodDeclare(&$data) {
        include 'declare/eav-inputmethod.php';
    }
    
    public static function onEavOutputmethodDeclare(&$data) {
        include 'declare/eav-outputmethod.php';
    }
    
}