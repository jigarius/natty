<?php

namespace Natty\Helper;

class FileHelper
extends \Natty\Uninstantiable {
    
    /**
     * Generates a deep directory name from an integer ID, such that a file
     * ends up in a directory xxx/xxx.
     * Example: if the ID is 123456789123456, then the directory name suggested
     * for the file would be 123/456/789/123/
     * @param double $id The ID for which to generate a directory name.
     * @param array $options [optional] An array of options including:
     * depth: Number of nested directories. By default, it is 2 and should
     * suffice for most cases. If your IDs are bigger, you might need some
     * other file storage structure.
     * @return string Directory name suggestion for the ID.
     */
    public static function generateDirname($id, array $options = array ()) {

        // Fallback to defaults
        $depth = isset ($options['depth'])
                ? $options['depth'] : $depth = 2;

        $id = str_pad($id, $depth*3, 0, STR_PAD_LEFT);
        $id_parts = str_split($id, 3);
        $id_parts = array_reverse($id_parts);

        $output = array_slice($id_parts, 0, $depth);
        return implode('/', $output);

    }
    
    public static function readUpload($key, $multiple = FALSE) {

        if ( !$upload_data = natty_array_get($_FILES, $key) )
            return FALSE;
        
        // Multiple uploads?
        if ( $multiple ) {
            $uploads = array ();
            if ( $multiple && !is_array($upload_data['name']) )
                return false;
            foreach ( $upload_data['name'] as $i => $foo ):
                $upload = array ();
                foreach ( array ('tmp_name', 'name', 'size', 'error', 'type') as $param ):
                    $upload[$param] = $upload_data[$param][$i];
                endforeach;
                if ( UPLOAD_ERR_OK != $upload['error'] )
                    continue;
                $uploads[] = $upload;
            endforeach;
            return $uploads;
        }
        else {
            $upload = $upload_data;
            if ( UPLOAD_ERR_OK != $upload['error'] )
                return false;
            return $upload;
        }
        
    }
    
    public static function glob($source, array $options = array ()) {
        
        $source = self::instancePath($source);
        
        // Replace variables in destination
        $variables = array (
            'filename' => '*',
            'extension' => '*',
            'variant' => 'orig'
        );
        
        // Override variables with the ones provided
        if ( isset ($options['variables']) )
            $variables = array_merge($variables, $options['variables']);
        
        $source_orig = natty_replace($variables, $source);
        $output = glob($source_orig);
        
        // Expecting a unique file?
        if ( isset ($options['unique']) ):
            return 1 === sizeof($output)
                ? $output[0] : FALSE;
        endif;
        
        return $output;
        
    }
    
    /**
     * Moves a temporary file to a specified directory, usually within the
     * root directory of the active instance.
     * @param array $upload An uploaded file array as in $_FILES. Must be data
     * for only one file - multiple file must not be passed at once.
     * @param string $dest Destination filename
     * @param string $options [optional] Additional options including<br />
     * variables: Associative array of variable replacements in destination. 
     * Replacements would be made via natty_replace();<br />
     * variants: Sizes to which the file should be resized (only for images).
     * This should be an associative array with the index as size name (this 
     * would act as the "size" variable in filename. "orig" is reserved for the 
     * original image) and maximum allowed pixels (on the longer side) as 
     * value. Example: 'resize' => array ('b' => 800, 's' => 400);
     * @return bool True or false.
     */
    public static function moveUpload(array $upload, $dest, array $options = array ()) {
        
        // File must have a tmp_name
        if ( !isset ($upload['tmp_name']) )
            natty_debug($upload);
        
        // Get upload info
        $upload_info = pathinfo($upload['name']);
        
        // Get destination info
        $dest = self::instancePath($dest);
        $dest_info = pathinfo($dest);
        
        // Replace variables in destination
        $vars_orig = array (
            'filename' => md5(uniqid(NULL, TRUE)),
            'extension' => strtolower($upload_info['extension']),
            'variant' => 'orig'
        );
        
        // Override variables with the ones provided
        if ( isset ($options['variables']) )
            $vars_orig = array_merge($vars_orig, $options['variables']);
        
        // Create destination directory, if required
        if ( !is_dir($dest_info['dirname']) && !mkdir($dest_info['dirname'], 0755, TRUE) )
            throw new \RuntimeException('Could not create storage directory for upload!');
        
        // Determine destination as per variables
        $dest_orig = natty_replace($vars_orig, $dest);
        if ( !$status = copy($upload['tmp_name'], $dest_orig) )
            return FALSE;
        
        // For image upload-specific operations
        if ( isset ($options['image']) ):
            
            // Re-format the image if needed
            if ( isset ($options['reformat']) && $vars_orig['extension'] != $options['reformat'] ):
                $vars_orig['extension'] = $options['reformat'];
                natty_debug('Reformat!');
            endif;
            
            // Resize the image if needed
            if ( isset ($options['variants']) ):
                foreach ( $options['variants'] as $size => $dimension ):
                    // Numeric size names mean the dimension defines size name
                    if ( is_numeric($size) )
                        $size = $dimension;
                    // Determine filename
                    $vars_size = $vars_orig;
                    $vars_size['variant'] = $size;
                    $dest_size = natty_replace($vars_size, $dest);
                    ImageHelper::resize($dest_orig, array (
                        'width' => $dimension,
                        'height' => $dimension,
                        'destination' => $dest_size,
                    ));
                endforeach;
            endif;
            
        endif;
        
        return $dest_orig;
        
    }
    
    /**
     * Performs a traditional unlink on the given filename with additional
     * functionality attachable via the second argument.
     * @param string $filename The file to unlink
     * @param array $options An associative array of options including:<br />
     * variants: If set to true, and if the filename has an "-orig.extension" ending,
     * all files in the directory with the same filename, ending with 
     * "-*.extension" would be unlinked as well.
     * @return bool True or false
     */
    public static function unlink($filename, array $options = array ()) {
        
        // Absolute path to a file to delete
        $filename = self::instancePath($filename);
        if ( is_file($filename) && !unlink($filename) )
            return FALSE;
        
        // Remove size variations, if it is an image
        if ( isset ($options['variants']) ):
            
            // Rewrite basename with "*" instead of "orig"
            $basename = basename($filename);
            $var_pattern = preg_replace('/^(.*)\-(orig)\.([a-z]+)$/', '$1-*.$3', $basename, 1);
            
            // Read files variation files, if "orig" was detected
            if ( $var_pattern != $basename ):
                $var_pattern = dirname($filename) . '/' . $var_pattern;
                $var_files = glob($var_pattern);
                foreach ( $var_files as $var_file ):
                    if ( !unlink($var_file) )
                        return FALSE;
                endforeach;
            endif;
            
        endif;
        
        return TRUE;
        
    }
    
    /**
     * Takes an ".orig.extension" type pathname / filename and swaps the "o"
     * before the extension with the given variant name.
     * @param string $original
     * @param string $variant
     * @return string Path to the variant
     */
    public static function variant($original, $variant) {
        return preg_replace('/^(.*)\.(orig)\.([a-z]+)$/', '$1.' . $variant . '.$3', $original, 1);
    }
    
    public static function info($filename) {
        
        if ( !is_file($filename) )
            return FALSE;
        
        $pathinfo = pathinfo($filename);
        
        $info = array (
            'location' => $filename,
            'name' => $pathinfo['basename'],
            'extension' => $pathinfo['extension'],
            'size' => filesize($filename)
        );
        
        return $info;
        
    }
    
    public static function instancePath($path, $base = 'root') {
        $site_path = 'instance/' . \Natty::readSetting('system--siteSlug') . '/';
        $site_path = \Natty::path($site_path, $base);
        return str_replace('instance://', $site_path, $path);
    }
    
    /**
     * Deletes the said directory along with its contents.
     * @param string $dirname Path to the directory within the instance.
     * @param boolean $delete_contents
     * @return type
     * @throws \InvalidArgumentException
     */
    public static function removeDir($dirname, $delete_contents = FALSE) {
        
        // Can only work within instances
        $dirname = self::instancePath($dirname, 'root');
        if ( 0 !== strpos($dirname, \Natty::readSetting('system--siteRoot')) )
            throw new \InvalidArgumentException(__METHOD__ . ' only works with instance:// paths for security reasons.');
        
        $output = TRUE;
        
        // Does the directory exist?
        if ( !is_dir($dirname) )
            return $output;
        
        // Delete the contents?
        if ( $delete_contents ):
            
            $content_coll = glob($dirname . '/*');
            foreach ( $content_coll as $path ):
                if ( is_dir($path) )
                    $output = $output && self::removeDir($path, $delete_contents);
                else
                    $output = $output && unlink($path);
            endforeach;
            
        endif;
        
        $output = $output && rmdir($dirname);
        return $output;
        
    }
    
    public static function protectDir($dirname) {
        
        if ( !is_dir($dirname) )
            throw new \RuntimeException('Directory does not exist.');
        
        $filename = $dirname . '/.htaccess';
        $content = "# Natty - Start
# ======

# Turn off all options we don't need
Options None
Options +FollowSymLinks

# Set global handler to prevent all executions
SetHandler Natty_Do_Nothing
<Files *>
    # Override handler again, if situation arises
    SetHandler Natty_Do_Nothing
</Files>

# If possible, disable the PHP engine entirely
<IfModule mod_php5.c>
    php_flag engine off
</IfModule>

# Natty - End
";
        
        $output = file_put_contents($filename, $content);
        if ( !$output )
            throw new \RuntimeException('File system permission error.');
        
    }
    
    /**
     * Returns whether a directory is empty or not.
     * @param string $dirname path/to/dir
     * @return boolean True if empty or false if not.
     */
    public static function isDirEmpty($dirname) {
        
        $output = TRUE;
        
        if ( !is_readable($dirname) ) {
            $output = FALSE;
        }
        else {
            $handle = opendir($dirname);
            while ( FALSE !== ($entry = readdir($handle)) ):
                if ( '.' !== $entry && '..' !== $entry ):
                    $output = FALSE;
                    break;
                endif;
            endwhile;
            closedir($handle);
        }
        
        return $output;
        
    }
    
}
