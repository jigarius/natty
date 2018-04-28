<?php

defined('NATTY') or die;

nimport('resource.file');

class NUploadedFile extends NFile {
    
    /**
     * Contains uploaded file data as in $_FILES['name']
     * @var array
     */
    protected $data;
    
    /**
     * Creates an NUploadedFile Object
     * @param string $data The $_FILES data for the file
     * @throws Exception If the file is not a valid upload
     * @return NUploadedFile
     */
    public function __construct( $data = null ) {
        
        if ( '' == @$data['name'] )
            throw new Exception(__CLASS__ . ' could not recognize uploaded file');
        
        $this->data = $data;
        
    }
    
    public function getExtension() {
        
        $basename = basename($this->data['name']);
        $dot_pos = strrpos($basename, '.');
        
        if (false === $dot_pos)
            return false;
        
        $ext = substr($basename, $dot_pos+1);
        
        if ( false == $ext )
            return false;
        
        return $force_lower
            ?strtolower($ext):$ext;
        
    }
    
    /**
     * Returns errors (if any) with the Uploaded File
     * @param void
     * @return false|string False if no error is found or an error message
     */
    public function getError() {
        
        $error = $this->data['error'];
        
        if ( UPLOAD_ERR_OK == $error )
            return false;
        
        if ( UPLOAD_ERR_INI_SIZE == $this->data['error'] || UPLOAD_ERR_FORM_SIZE == $this->data['error'] )
            return 'File too big! Please upload a smaller file!';
        
    }
    
    public function getName() {
        return $this->data['tmp_name'];
    }
    
    public function moveTo( $dest ) {
        // Attach uploaded file's extension if no extension is specified
        $filename = basename($dest);
        if ( false === strpos($filename, '.') )
            $dest .= '.' . $this->getExtension(1);
        return parent::moveTo($dest);
    }
    
}