<?php

defined('NATTY') or die;

/**
 * Abstract for files in a file-system
 * @author JigaR Mehta | Greenpill Productions
 */
class NFile extends NStdClass {
    
    /**
     * Stores size units for files
     * @staticvar array
     */
    static $sizeUnits = array ('B', 'KB', 'MB', 'GB');

    /**
     * Length of read-write buffers
     * @var int
     */
    protected $bufferSize = 2048;

    /**
     * Mode in which the file is open
     * @var string
     */
    protected $mode;

    /**
     * Name of the current file
     * @var string
     */
    protected $name;

    /**
     * The file pointer / handle for the opened file
     * @var resource
     */
    protected $pointer;

    const TYPE_ZIP = 'application/zip';
    const TYPE_JPG = 'image/jpeg';

    /**
     * Creates a new File Object
     * @param string $filename
     * @param string $mode
     * @return NFile NFile Object
     */
    public function __construct($filename, $mode = null) {
        $this->name = $filename;
        if ( !is_null($mode) )
            $this->open($mode);
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * Closes the file
     * @param void
     * @return void
     */
    public function close() {
        if (is_resource($this->pointer))
            fclose($this->pointer);
    }

    /**
     * Copies the file to a said destination
     * @param string $dest Destination filename
     * @param bool $return_object [optional] Whether to return the copied 
     * file object
     * @return NFile Reference to the copied file
     */
    public function copyTo( $dest, $return_object = false ) {
        
        if ( empty ($dest) )
            return false;
        
        // Verify existence of directory or create one
        $dest_dir = dirname($dest);
        if ( !is_dir($dest_dir) && !mkdir($dest_dir, null, true) )
            throw new BadMethodCallException(__METHOD__ . ' expects argument 1 to be a valid directory!');
        
        if ( !copy($this->getName(), $dest) )
            return false;
        
        if ( !$return_object )
            return true;
        
        $class = $this->getClass();
        return new $class($dest);
        
    }
    
    public function delete() {
        return unlink($this->getName());
    }
    
    /**
     * Tells whether end of file is reached
     * @param void
     * @return bool Yes or No
     */
    public function eof() {
        return feof($this->pointer);
    }

    /**
     * Moves the file to the said destination
     * @param string $path Destination filename
     * @return bool True on success or false on failure
     * @throws BadMethodCallException If the argument is not a valid directory
     */
    public function moveTo( $dest ) {
        
        if ( empty ($dest) )
            return false;
        
        // Verify existence of directory or create one
        $dest_dir = dirname($dest);
        if ( !is_dir($dest_dir) && !mkdir($dest_dir, null, true) )
            throw new BadMethodCallException(__METHOD__ . ' expects argument 1 to be a valid directory!');
        
        if ( !rename($this->getName(), $dest) )
            return false;
        
        $this->name = $dest;
        
    }
    
    /**
     * Returns the extension for the File
     * or false if no extension is found
     * @param bool $force_lower Returns in lower-case
     * @return string|bool Extension for the file or false
     */
    public function getExtension( $force_lower = false ) {
        
        $basename = basename($this->name);
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
     * Returns MIME Type of the file
     * @param void
     * @return string MIME Type
     */
    public function getMimeType() {
        $ext = $this->getExtension();
        if (false === $ext)
            return false;
        $ext = strtolower($ext);
        switch ($ext):
            case 'zip':
            case 'jpg':
                return $this->getStaticProperty('TYPE_' . strtoupper($ext));
                break;
            case 'jpeg':
                return self::TYPE_JPG;
                break;
        endswitch;
    }
    
    public function getPointer() {
        return $this->pointer;
    }
    
    public function getName() {
        return $this->name;
    }

    /**
     * Returns the approximate size of the file
     * @param void
     * @return string Size of the file (approx)
     */
    public function getApproxSize() {
        $size = filesize($this->name);
        $ui = 0;
        while ($size > 1024 && self::$sizeUnits[$ui] != 'GB') {
            $size = floor($size / 1024);
            $ui++;
        }
        return $size . self::$sizeUnits[$ui];
    }

    /**
     * Reads and returns all contents of the file
     * @param void
     * @return string
     */
    public function getContents() {
        if (!$this->pointer && !$this->open('r')):
            return false;
        endif;
        $this->seek(0);
        $result = '';
        while (!$this->eof())
            $result .= $this->read();
        return $result;
    }

    /**
     * Reads a line as CSV and returns it
     * @param void
     * @return array Values
     */
    public function getCsv() {
        $this->isOpen();
        return fgetcsv($this->pointer);
    }
    
    public function hasExtension( $ext ) {
        $ext = $this->getExtension();
        $exts = func_get_args();
        foreach ( $exts as $t_ext ):
            if ( $ext == $t_ext )
                return true;
        endforeach;
        return false;
    }

    /**
     * Opens the file in the given mode
     * @param string $mode The mode to open the file in
     * @return resource|bool Pointer to the file or false
     */
    public function open($mode) {
        $this->mode = $mode;
        $this->pointer = fopen($this->name, $mode);
        return $this->pointer;
    }

    /**
     * Puts given contents to the file
     * @param string $data to be written to the file
     * @return void
     */
    public function putContents($data) {
        $this->close();
        // Open the document in write mode
        if (!$this->open('w'))
            return false;
        // Write the data and return length of stream output
        for ($written = 0; $written < strlen($data); $written += $twl) {
            $twl = $this->write(substr($data, $written));
            if (false === $twl)
                return $written;
        }
        return $written;
    }

    /**
     * Reads a chunk of data from as buffer
     * @param void
     * @return string Data
     */
    public function read() {
        return fread($this->pointer, $this->bufferSize);
    }

    /**
     * Offset to seek from the beginning of the file
     * @param int $offset
     * @return void */
    public function seek($offset) {
        fseek($this->pointer, $offset);
    }

    /**
     * Sets the buffer size for file operations
     * @param int $length Length of buffer
     * @return void
     */
    public function setBufferSize($length) {
        $this->bufferSize = (int) $length;
    }

    /**
     * Verifies whether a stream is open
     * @ignore
     * @param void
     * @return bool Yes or No
     */
    private function isOpen() {
        if (!is_resource($this->pointer))
            die(__CLASS__ . ' could expects an open stream for operation!');
        return true;
    }

    /**
     * Writes the passed data into the file
     * @param string $data Data to be written
     * @return int|bool Length of data written or false
     */
    public function write($data) {
        return fwrite($this->pointer, $data);
    }

}