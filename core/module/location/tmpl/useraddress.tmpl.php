<?php

defined('NATTY') or die;

$line_city = array ();
if ( $entity->city )
    $line_city[] = $entity->city;
if ( $entity->postCode )
    $line_city[] = $entity->postCode;

$line_state = array ();
if ( $entity->stateName )
    $line_state[] = $entity->stateName;
if ( $entity->countryName )
    $line_state[] = $entity->countryName;

?>
<div class="<?php echo $classes; ?>">
    <?php if ( isset ($options['heading']) ): ?>
    <h3><?php echo $entity->name; ?></h3>
    <?php endif; ?>
    <div class="prop-body">
        <?php echo nl2br($entity->body); ?>
        <?php if ($entity->landmark): ?>
            <br /><em>Landmark: </em>
            <?php if ( $entity->landmark ) echo $entity->landmark; ?>
        <?php endif; ?>
    </div>
    <?php if ( sizeof($line_city) > 0 ): ?>
    <div class="prop-city"><?php echo implode(' ', $line_city); ?></div>
    <?php endif; ?>
    <?php if ( sizeof($line_state) > 0 ): ?>
    <div class="prop-state"><?php echo implode(', ', $line_state); ?></div>
    <?php endif; ?>
</div>