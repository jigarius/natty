<?php

defined('NATTY') or die;

use Natty\ORM\EntityObject;

$handler = Natty::getHandler('example--course');

/**
 * Deleting techniques
 */
$entities = $handler->read();
foreach ( $entities as $entity ):
    $entity->delete();
endforeach;
unset ($entity);

/**
 * Here, we would work with only two languages
 */
$lid_default = \Natty::getOutputLangId();
$lid_enuk = 'en-UK';

/**
 * Create the entity in primary language; It is advisable to have it created
 * in the site's default language at the time of creation.
 */
$entity_default = $handler->create(array (
    'isNew' => TRUE,
    'cid' => 1,
    'code' => 'PNT',
    'tsCreated' => time(),
    // This sets the language of creation
    'ail' => $lid_default,
    'name' => 'Art',
    'description' => 'Art classes for everyone - children and adults!',
    'junkField' => 'This would be ignored'
));
$handler->save($entity_default);

/**
 * Add entity translation for a specific language
 */
$entity_enuk = $entity_default->getClone();
$entity_enuk->ail = $lid_enuk;
$entity_enuk->name = 'Painting';
$entity_enuk->description = 'Painting classes for everyone - small and big!';
$entity_enuk->save();

/**
 * Read the entity in a particular language and update the translation
 */
$entity_enuk = $handler->readById(1, array (
    'language' => 'en-UK'
));
$entity_enuk->description = 'Art and color stuff. We dont understand all that!';
$entity_enuk->save();

highlight_file(__FILE__);

natty_debug();