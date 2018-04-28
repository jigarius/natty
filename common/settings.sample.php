<?php

defined('NATTY') or die;

/**
 * Whether Natty has been installed.
 */
$data['system--installed'] = '@system--installed';

/**
 * Key for Encryption/decryption
 */
$data['system--cipherKey'] = '@system--cipherKey';

/**
 * Database configuration
 */
$data['system--database'] = array ();

// Default database
$data['system--database']['default'] = array (
    'driver' => '@system--database:default:driver',
    'host' => '@system--database:default:host',
    'dbname' => '@system--database:default:dbname',
    'prefix' => '@system--database:default:prefix',
    'username' => '@system--database:default:username',
    'password' => '@system--database:default:password',
);