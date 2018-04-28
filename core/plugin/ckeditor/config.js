/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.allowedContent = true; 
    config.toolbar = 'Full';
    config.toolbar_Basic =
    [
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
        { name: 'alignment', items : [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
        { name: 'tools', items : [ 'Source', 'Maximize', 'About' ] },
        '/',
        { name: 'colors', items : [ 'TextColor','BGColor' ] },
        { name: 'links', items : [ 'Link','Unlink' ] },
        { name: 'about', items: ['About'] }
    ];
    
    config.toolbar_Full =
    [
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
        { name: 'alignment', items : [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote' ] },
        { name: 'tools', items : [ 'Source', 'Maximize', 'About' ] },
        '/',
        { name: 'colors', items : [ 'TextColor','BGColor' ] },
        { name: 'links', items : [ 'Link','Unlink' ] },
        { name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','SpecialChar','Iframe' ] },
        { name: 'styles', items : [ 'Styles','Format','FontSize' ] }
	
    ];
};