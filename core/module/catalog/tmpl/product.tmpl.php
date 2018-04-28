<?php

defined('NATTY') or die;

$mod_store = \Natty::getPackage('module', 'store');

// Open wrapper
echo '<div class="' . $classes . '">';

// Render heading if not in page mode
if ( !$options['page'] )
    echo $heading;

// Render images
if ( isset ($build['mediaImage']) ):
    echo '<div class="image-cont">';
    echo natty_render($build['mediaImage']);
    unset ($build['mediaImage']);
    echo '</div>';
endif;

if ( 'default' === $options['viewMode'] ):
    
    echo '<div class="form-cont">';
    
    // Render summary
    if ( isset ($build['eavSummary']) ):
        echo natty_render($build['eavSummary']);
        echo '</div>';
        unset ($build['eavSummary']);
    endif;
    
    // Render ordering form (if any)
    if ( isset ($form) )
        echo natty_render($form);
    
    '</div>';
    
endif;

// Other attributes and stuff
echo '<div class="data-cont">';
natty_render($build);
echo '</div>';

// End wrapper
echo '</div>';