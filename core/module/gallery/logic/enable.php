<?php

defined('NATTY') or die;

if ( \Natty::readSetting('gallery--installing') ):
    
    $ctype_handler = \Natty::getHandler('cms--contenttype');
    
    // Content-type "gallery-album"
    $ctype_album = $ctype_handler->readById('gallery-album');
    if ( $ctype_album )
        \Natty\Console::message('Content-type "gallery-album" already existed and has been claimed by the Gallery module.');
    
    if ( !$ctype_album ):
        
        $ctype_album = $ctype_handler->create(array (
            'isNew' => 1,
            'ctid' => 'gallery-album',
            'name' => 'Gallery Album',
            'description' => 'Albums containing various media like photos and videos.',
        ));
        
    endif;
    
    $ctype_album->module = 'gallery';
    $ctype_album->isLocked = 1;
    $ctype_album->save();
    
    // Content-type "gallery-item"
    $ctype_item = $ctype_handler->readById('gallery-item');
    if ( $ctype_item )
        \Natty\Console::message('Content-type "gallery-item" already existed and has been claimed by the Gallery module.');
    
    if ( !$ctype_item ):
        
        $ctype_item = $ctype_handler->create(array (
            'isNew' => 1,
            'ctid' => 'gallery-item',
            'name' => 'Gallery Item',
            'description' => 'Photos, videos and items which appear inside Gallery Albums.',
        ));
        
    endif;
    
    $ctype_item->module = 'gallery';
    $ctype_item->isLocked = 1;
    $ctype_item->save();
    
endif;