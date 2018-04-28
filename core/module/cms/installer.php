<?php

namespace Module\Cms;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__cms_block_content',
            'description' => 'Content for custom blocks',
            'columns' => array (
                'biid' => array (
                    'description' => 'Block Instance ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned')
                ),
                'ail' => array (
                    'description' => 'Language for the content',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'content' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('biid', 'ail'),
                )
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__cms_menu',
            'description' => 'Menu data',
            'columns' => array (
                'mid' => array (
                    'description' => 'Menu ID',
                    'type' => 'int',
                    'length' => 5,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'mcode' => array (
                    'description' => 'Menu code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'isLocked' => array (
                    'description' => 'Whether the menu is editable from the backend.',
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('mid')
                ),
                'mcode' => array (
                    'columns' => array ('mcode'),
                    'unique' => TRUE,
                )
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__cms_menu_i18n',
            'description' => 'Menu i18n data',
            'columns' => array (
                'mid' => array (
                    'description' => 'Menu ID',
                    'type' => 'int',
                    'length' => 5,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'mcode' => array (
                    'description' => 'Menu code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'ail' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('mid', 'ail')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__cms_menuitem',
            'description' => 'Menu item data.',
            'columns' => array (
                'miid' => array (
                    'description' => 'Menuitem ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment')
                ),
                'mid' => array (
                    'description' => 'Menu ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'mcode' => array (
                    'description' => 'Menu code',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'parentId' => array (
                    'description' => 'Parent MenuItem ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'nullable')
                ),
                'level' => array (
                    'description' => 'Vertical position of the item in the menu tree.',
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                    'flags' => array ('unsigned')
                ),
                'href' => array (
                    'description' => 'Target location of the menu item.',
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'ooa' => array (
                    'description' => 'Horizontal position of the item in the branch.',
                    'type' => 'int',
                    'length' => 10,
                ),
                'sdata' => array (
                    'description' => 'Serialized data',
                    'type' => 'text',
                ),
                'isLocked' => array (
                    'description' => 'Whether the link is editable from the backend.',
                    'type' => 'int',
                    'length' => 1,
                    'default' => 0,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('miid')
                ),
                'mid' => array (
                    'columns' => array ('mid'),
                ),
                'mcode' => array (
                    'columns' => array ('mcode'),
                ),
            )
        ));
        $schema_helper->createTable(array (
            'name' => '%__cms_menuitem_i18n',
            'description' => 'Menu item i18n data.',
            'columns' => array (
                'miid' => array (
                    'description' => 'MenuItem ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment')
                ),
                'ail' => array (
                    'description' => 'Language ID',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'markup' => array (
                    'description' => 'Markup for the item.',
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 1,
                )
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('miid', 'ail')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__cms_contenttype',
            'description' => 'Content-type registry.',
            'columns' => array (
                'ctid' => array (
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'module' => array (
                    'description' => 'The declaring module.',
                    'type' => 'varchar',
                    'length' => 32,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'isCustom' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1
                ),

            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('ctid')
                )
            )
        ));

        $schema_helper->createTable(array (
            'name' => '%__cms_content',
            'description' => 'Content data.',
            'columns' => array (
                'cid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('increment')
                ),
                'ctid' => array (
                    'description' => 'FK: cms_contenttype.ctid',
                    'type' => 'varchar',
                    'length' => 32
                ),
                'idCreator' => array (
                    'description' => 'ID of the creator.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned')
                ),
                'dtCreated' => array (
                    'description' => 'Time created.',
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'dtModified' => array (
                    'description' => 'Time modified.',
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'dtPublished' => array (
                    'description' => 'Time published.',
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable')
                ),
                'isPromoted' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
                'status' => array (
                    'description' => '0 = Unpublished, 1 = Published',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid')
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__cms_content_i18n',
            'description' => 'Content translations.',
            'columns' => array (
                'cid' => array (
                    'type' => 'int',
                    'length' => 20,
                ),
                'ail' => array (
                    'type' => 'varchar',
                    'length' => 8
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),

            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'ail')
                )
            )
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__cms_block_content');
        $schema_helper->dropTable('%__cms_contenttype');
        $schema_helper->dropTable('%__cms_content');
        $schema_helper->dropTable('%__cms_content_i18n');
        $schema_helper->dropTable('%__cms_menu');
        $schema_helper->dropTable('%__cms_menu_i18n');
        $schema_helper->dropTable('%__cms_menuitem');
        $schema_helper->dropTable('%__cms_menuitem_i18n');
        
    }
    
    public static function enable() {
        
        // First run?
        if ( \Natty::readSetting('cms--installing') ):

            $menu_handler = \Natty::getHandler('cms--menu');
            $content_handler = \Natty::getHandler('cms--content');
            $ctype_handler = \Natty::getHandler('cms--contenttype');

            // Create static "page"
            $ctype_page = $ctype_handler->createAndSave(array (
                'isNew' => TRUE,
                'ctid' => 'page',
                'module' => 'cms',
                'name' => 'Static Page',
                'description' => 'Static pages on your website. Example: About us, Terms & Conditions.',
            ));
            
            // Create home page
            $content_handler->createAndSave(array (
                'ail' => \Natty::getInputLangId(),
                'idCreator' => 1,
                'name' => 'Home',
                'status' => 1,
            ));

            // Create main menu
            $menu_main = $menu_handler->createAndSave(array (
                'ail' => 'en-US',
                'mcode' => 'main-menu',
                'name' => 'Main menu',
                'isLocked' => 1,
            ));

        endif;
        
    }
    
}