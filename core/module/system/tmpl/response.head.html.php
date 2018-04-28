<?php defined('NATTY') or die; ?>
<head>
<?php

// Window Title
if ($isHome) {
    $window_title = $system_siteName;
    if ($system_siteCaption)
        $window_title .= ' - ' . $system_siteCaption;
}
else {
    $window_title = isset ($attributes['title']) 
            ? $attributes['title'] : 'Untitled Document';
    $window_title .= ' | ' . \Natty::readSetting('system--siteName', $_SERVER['SERVER_NAME']);
}
unset ($attributes['title']);

// Language
$attributes['language'] = isset ($attributes['language'])
        ? $attributes['language'] : \Natty::readSetting('system--siteLanguage');
// Charset
$attributes['charset'] = isset ( $attributes['charset'] )
        ? $attributes['charset'] : 'UTF-8';
// Content Type
$attributes['Content-Type'] = isset ($attributes['Content-Type'])
        ? $attributes['Content-Type'] : 'text/html;charset=' . $attributes['charset'];
// Generator
$attributes['generator'] = isset ($attributes['generator']) 
        ? $attributes['generator'] : 'Natty';

// Render the Title
echo '<title>' . strip_tags($window_title) . '</title>';

// Standard http-equiv meta tags
$_http_equivs = array ('Content-Type');
foreach ( $_http_equivs as $name ):
    if ( !$attributes[$name] )
        continue;
    echo '<meta http-equiv="' . $name . '" content="' . $attributes[$name] . '"/>';
    unset ($attributes[$name]);
endforeach;

// Meta data from attributes
foreach ( $attributes as $name => $content ):
    echo '<meta name="' . $name . '" content="' . $content . '"/>';
endforeach;

// Other meta data
if ( isset ($head['meta']) ):
    foreach ( $head['meta'] as $t_meta ):
        $t_meta['_element'] = 'meta';
        echo natty_render_element($t_meta);
    endforeach;
endif;

// Add stylesheets
if ( isset ($head['stylesheet']) ):
    foreach ( $head['stylesheet'] as $t_stylesheet ):
        $t_stylesheet['_element'] = 'link';
        echo natty_render_element($t_stylesheet);
    endforeach;
endif;

// Add scripts
if ( isset ($head['script']) ):
    $scripts_system = '';
    $scripts_general = '';
    foreach ( $head['script'] as $t_script ):
        $t_script['_element'] = 'script';
        if ( 'system' == $t_script['_type'] ) {
            $scripts_system .= natty_render_element($t_script);
        }
        else {
            $scripts_general .= natty_render_element($t_script);
        }
    endforeach;
    echo $scripts_system;
    echo $scripts_general;
endif;

?>
</head>