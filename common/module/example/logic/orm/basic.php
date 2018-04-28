<?php

defined('NATTY') or die;

use Natty\ORM\EntityObject;

$handler = Natty::getHandler('example--school');

/**
 * Deleting techniques
 */
$entities = $handler->read();
foreach ( $entities as $entity ):
    $entity->delete();
endforeach;

/**
 * Creation techniques
 */
$entity = $handler->create(array (
    'isNew' => TRUE,
    'sid' => 1,
    'name' => 'Demo High School',
    'description' => 'This entity has been freshly inserted!',
    'status' => 0,
    'junkField' => 'This would be ignored'
));
$entity->save();

// Basic reading method
$entity = $handler->read(array (
        'conditions' => array ('sid', '=', ':sid'),
        'parameters' => array ('sid' => 1),
        'unique' => true
));

// Reading using key-value pairs
$entity = $handler->readByKeys(
        array ('sid' => 1),
        array ('unique' => true)
);

// Reading by identifier; Utilizes static caching to avoid multiple queries
// for entities which have already been loaded once.
$entity = $handler->readById(1);

/**
 * Update techniques
 */
$entity->name = 'Kamicolo High School';
$entity->description = 'This entity has been updated!';
$entity->status = 1;
$entity->save();

highlight_file(__FILE__);

natty_debug();