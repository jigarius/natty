<?php

namespace Module\Catalog;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {

        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__catalog_product',
            'description' => 'Product data.',
            'columns' => array (
                'pid' => array (
                    'description' => 'Product ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'pcode' => array (
                    'description' => 'Product Code',
                    'type' => 'varchar',
                    'length' => 64,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'ptid' => array (
                    'description' => 'Product Type ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'cid' => array (
                    'description' => 'Category ID. FK: taxonomy_term.tid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'costPrice' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'salePrice' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                ),
                'trid' => array (
                    'description' => 'Tax Rule ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'length' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'breadth' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'height' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'weight' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'shippingCharge' => array (
                    'description' => 'Additional shipping charges.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => NULL,
                    'flags' => array ('unsigned', 'nullable'),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                    'flags' => array ('signed'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('pid'),
                ),
                'pcode' => array (
                    'columns' => array ('pcode'),
                    'unique' => 1,
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__catalog_product_i18n',
            'description' => 'Product i18n data.',
            'columns' => array (
                'pid' => array (
                    'description' => 'Product ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
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
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('pid', 'ail')
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__catalog_product_category_map',
            'description' => 'Product category association.',
            'columns' => array (
                'pid' => array (
                    'description' => 'Product ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'cid' => array (
                    'description' => 'Category ID. FK: taxonomy_term.tid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('pid', 'cid'),
                )
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__catalog_producttype',
            'description' => 'Product type data.',
            'columns' => array (
                'ptid' => array (
                    'description' => 'Product Type ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'status' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                    'flags' => array ('signed'),
                ),
                'isLocked' => array (
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('ptid'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__catalog_producttype_i18n',
            'description' => 'Product type i18n data.',
            'columns' => array (
                'ptid' => array (
                    'description' => 'Product Type ID',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
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
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('ptid', 'ail')
                ),
            ),
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__catalog_product');
        $schema_helper->dropTable('%__catalog_product_i18n');
        $schema_helper->dropTable('%__catalog_producttype');
        $schema_helper->dropTable('%__catalog_producttype_i18n');
        
    }
    
    public static function enable() {
        
        // First run?
        if ( \Natty::readSetting('catalog--installing') ):

            $tgroup_handler = \Natty::getHandler('taxonomy--group');

            // Create categories group
            $tgroup_categories = $tgroup_handler->read(array (
                'key' => array ('gcode' => 'catalog-categories'),
                'unique' => TRUE,
            ));

            if ( !$tgroup_categories ) {
                $tgroup_categories = $tgroup_handler->create(array (
                    'ail' => 'en-US',
                    'gcode' => 'catalog-categories',
                    'name' => 'Catalog Categories',
                    'description' => 'Categories for products in your catalog.',
                ));
            }
            else {
                \Natty\Console::message('The taxonomy group "catalog-categories" was claimed by the Catalog module.');
            }

            $tgroup_categories->module = 'catalog';
            $tgroup_categories->isLocked = 1;

            $tgroup_categories->save();

            // Create default product type
            $ptype_handler = \Natty::getHandler('catalog--producttype');
            $ptype_handler->createAndSave(array (
                'ail' => 'en-US',
                'name' => 'Default',
                'status' => 1,
                'isLocked' => 1,
            ));

        endif;
        
    }
    
    public static function disable() {
        
    }
    
}