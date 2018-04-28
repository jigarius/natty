<?php

defined('NATTY') or die;

nimport('resource.file');

/**
 * Abstract for images
 * @author JigaR Mehta | Greenpill Productions
 */
class NImage extends NFile {
    
    /**
     * Image resource handle
     * @var resource
     */
    protected $pointer;
    
    protected $height;
    
    protected $width;
    
    /**
     * Creates an NImage object
     * @param string $filename Name for / of the file
     * @return NImage The image object
     */
    public function __construct( $filename ) {
        parent::__construct($filename);
    }
    
    public function __destruct() {
        $this->close();
    }
    
    public function close() {
        if ( is_resource($this->pointer) )
            imagedestroy ($this->pointer);
    }
    
    /**
     * Creates an Image object from the specified filename
     * @param string $extension [optional] The extension to assume if the
     * extension is not a standard image extension
     * @throws Exception If file extension is unrecognized
     */
    public function createFromFile( $extension = null ) {
        
        $filename = $this->getName();
        $ext = ( is_null($extension) )
                ?$this->getExtension(1):$extension;
        
        switch ( $ext ):
            case 'jpg':
                $this->pointer = imagecreatefromjpeg( $filename );
                break;
            default:
                throw new Exception(__METHOD__ . ' could not understand image extension "' . $ext . '"!');
                break;
        endswitch;
        
    }
    
    public function createTrueColor($width, $height) {
        $this->pointer = imagecreatetruecolor($width, $height);
        $this->width = $width;
        $this->height = $height;
    }
    
    public function copyResampled( NImage $src, $src_x = null, $src_y = null, $dst_x = null, $dst_y = null ) {
        
        $src_x = natty_vod($src_x, 0);
        $src_y = natty_vod($src_y, 0);
        $dst_x = natty_vod($dst_x, 0);
        $dst_y = natty_vod($dst_y, 0);
        
        $src_width = $src->getWidth();
        $src_height = $src->getHeight();
        $dst_width = $this->getWidth();
        $dst_height = $this->getHeight();
        
        return imagecopyresampled($this->pointer, $src->getPointer(), $dst_x, $dst_y, $src_x, $src_y, $dst_width, $dst_height, $src_width, $src_height );
        
    }
    
    public function render( $filename = null ) {
        
        $ext = $this->getExtension();
        
        switch ( $ext ):
            case 'jpg':
                return $this->renderJpg($filename);
                break;
        endswitch;
        
    }
    
    public function renderJpg( $filename, $quality = 80 ) {
        return imagejpeg($this->pointer, $filename, $quality);
    }
    
    public function saveVariant( $width, $height = null, $preserve_ratio = null, $filename = null ) {
        
        $aratio = $this->getAspectRatio();
        $preserve_ratio = is_null($preserve_ratio) 
            ?true:$preserve_ratio;
        
        if ( !is_numeric($width) )
            throw new BadMethodCallException(__METHOD__ . ' expects argument 1 to be an integer!');
        
        $height = is_null($height)
            ? floor($width / $aratio) : $height;
        
        // If ratio was to be preserved (true by default)
        if ( $preserve_ratio ):
            
            // Target dimensions
            $t_width = $height * $aratio;
            $t_height = $width / $aratio;
            
            // Fit to target dimensions
            if ( $t_height > $height ) {
                $width = $height * $aratio;
            }
            elseif ( $t_width > $width ) {
                $height = $width / $aratio;
            }
            
            unset ($t_width, $t_height);
            
        endif;
        
        // Determine destination default filename: filename_width.ext
        if ( is_null($filename) ):
            $filename = $this->getName();
            $ext = $this->getExtension();
            $filename = preg_replace('/.jpg$/', '-' . func_get_arg(0) . ".{$ext}", $filename);
        endif;
        
        $variant = new NImage($filename);
        $variant->createTrueColor($width, $height);
        $variant->copyResampled($this);
        return $variant->render($filename);
        
    }
    
    public function getAspectRatio() {
        $ratio = $this->getWidth() / $this->getHeight();
        return $ratio;
    }
    
    public function getHeight() {
        return imagesy($this->pointer);
    }
    
    public function getWidth() {
        return imagesx($this->pointer);
    }
    
}