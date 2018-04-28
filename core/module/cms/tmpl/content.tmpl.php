<?php

defined('NATTY') or die;

?><div class="<?php echo $classes; ?>">
<?php

// Render heading if not in page mode
if ( !$options['page'] )
    echo $heading;

// Render attributes
echo natty_render($build);

// Display links
if ( $options['links'] )
    echo natty_render($links);

?></div>