<?php

namespace Module\Media\Classes;

class ImagestyleHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    protected static $imageProcCache = NULL;
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'media--imagestyle',
            'tableName' => '%__media_imagestyle',
            'modelName' => array ('image style', 'image styles'),
            'keys' => array (
                'id' => 'isid',
            ),
            'properties' => array (
                'isid' => array (),
                'iscode' => array (),
                'ail' => array ('isTranslatable' => 1),
                'name' => array ('isTranslatable' => 1),
                'isLocked' => array (),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        
        if ( $user->can('media--administer imagestyle entities') ):
            $output['configure'] = '<a href="' . \Natty::url('backend/media/image-style/' . $entity->isid . '/processors') . '">Configure</a>';
        endif;
        
        return $output;
        
    }
    
    public static function readImageProcSettings(array $options = array ()) {
        
        if ( !is_array(self::$imageProcCache) || isset ($options['nocache']) ):

            $dbo = \Natty::getDbo();
            $iproc_coll = $dbo->read('%__media_imagestyle_proc', array (
                'conditions' => '1=1',
                'ooa' => 'asc',
            ));

            self::$imageProcCache = array ();
            foreach ( $iproc_coll as $record ):
                
                if ( !isset (self::$imageProcCache[$record['isid']]) )
                    self::$imageProcCache[$record['isid']] = array ();
                
                $record['settings'] = unserialize($record['settings']);
                self::$imageProcCache[$record['isid']][$record['ipid']] = $record;
                
            endforeach;
            
        endif;
        
        // Return unique record
        if ( isset ($options['isid']) ):
            $isid = $options['isid'];
            return self::$imageProcCache[$isid];
        endif;
        
        return self::$imageProcCache;
        
    }
    
    public static function writeImageProcSettings(array &$definition) {
        
        // Pre-process record
        $sample_record = array (
            'isid' => NULL,
            'ipid' => NULL,
            'ooa' => 0,
            'settings' => array (),
        );
        
        $definition = array_intersect_key($definition, $sample_record);
        $definition = array_merge($sample_record, $definition);
        
        // Upsert the record
        $record = $definition;
        $record['settings'] = serialize($record['settings']);
        
        $dbo = \Natty::getDbo();
        $dbo->upsert('%__media_imagestyle_proc', $record, array (
            'keys' => array ('isid', 'ipid'),
        ));
        
        // Clear static cache
        self::$imageProcCache = NULL;
        
    }
    
    /**
     * Creates the given image style for a given source file and saves it at
     * the given destination.
     * @param \Natty\ORM\EntityObject media--imagestyle object.
     * @param string $filename
     * @param string $destination
     */
    public function createImageStyle($entity, $filename, $destination) {
        
        $this->isIdentifiable($entity, TRUE);
        
        // Destination directory must be writable
        $dest_dirname = dirname($destination);
        if ( !is_writable($dest_dirname) )
            throw new \Natty\Core\FilePermException();
        
        // Create destination file
        $imagedata = \Natty\Helper\ImageHelper::createFromFile($filename);
        
        // Read processors in this style
        $iproc_applied_coll = $this::readImageprocSettings(array (
            'isid' => $entity->isid,
        ));
        
        // Read image processor data
        $iproc_coll = \Module\Media\Controller::readImageProc();
        
        // Apply each processor, one by one with their saved settings
        foreach ( $iproc_applied_coll as $iproc_applied ):
            
            $ipid = $iproc_applied['ipid'];
            if ( !isset ($iproc_coll[$ipid]) )
                continue;
            $iproc = $iproc_coll[$ipid];
            
            $iproc['helper']::applyToImage($imagedata, $iproc_applied['settings']);
            
        endforeach;
        
        \Natty\Helper\ImageHelper::saveToFile($imagedata, $destination);
        
    }
    
}