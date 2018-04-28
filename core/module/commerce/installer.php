<?php

namespace Module\Commerce;

class Installer
extends \Natty\Core\PackageInstaller {
    
    public static function install() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->createTable(array (
            'name' => '%__commerce_cartitem',
            'description' => 'Cart item data.',
            'columns' => array (
                'ciid' => array (
                    'description' => 'Cart item ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'idProduct' => array (
                    'description' => 'Product ID. FK: cataog_product.pid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idSession' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'idCreator' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idCustomer' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idCurrency' => array (
                    'type' => 'varchar',
                    'length' => 8,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'xRate' => array (
                    'type' => 'decimal',
                    'length' => 10,3,
                    'default' => 1,
                    'flags' => array ('unsigned'),
                ),
                'name' => array (
                    'description' => 'A heading for the cart item. Usually the product name.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'rate' => array (
                    'description' => 'Unit price of the item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                ),
                'quantity' => array (
                    'description' => 'Number of units.',
                    'type' => 'int',
                    'length' => 10,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'unitWeight' => array (
                    'description' => 'Unit weight.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'totalWeight' => array (
                    'description' => 'Total weight.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountProduct' => array (
                    'description' => 'Rate x Quantity.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountDiscount' => array (
                    'description' => 'Total discount for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountShipping' => array (
                    'description' => 'Total shipping for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountTax' => array (
                    'description' => 'Total taxes for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountFinal' => array (
                    'description' => 'Final value for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'idCarrier' => array (
                    'description' => 'Shipment carrier ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'sdata' => array (
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'status' => array (
                    'description' => '1 = Active, 0 = Removed',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('ciid'),
                ),
                'idCreator' => array (
                    'columns' => array ('idCreator'),
                ),
                'idSession' => array (
                    'columns' => array ('idSession'),
                ),
            ),
        ));

        $schema_helper->createTable(array (
            'name' => '%__commerce_order',
            'description' => 'Order data.',
            'columns' => array (
                'oid' => array (
                    'description' => 'Order ID',
                    'type' => 'int',
                    'length' => 20,
                    'default' => NULL,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'ocode' => array (
                    'description' => 'Order code',
                    'type' => 'varchar',
                    'length' => 32,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'idCreator' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idCustomer' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'shippingName' => array (
                    'description' => 'Name for shipping.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'idShippingAddress' => array (
                    'description' => 'Shipping Address ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'billingName' => array (
                    'description' => 'Name for billing.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'idBillingAddress' => array (
                    'description' => 'Billing Address ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idCurrency' => array (
                    'type' => 'varchar',
                    'length' => 8,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'xRate' => array (
                    'type' => 'decimal',
                    'length' => 10,3,
                    'default' => 1,
                    'flags' => array ('unsigned'),
                ),
                'amountProduct' => array (
                    'description' => 'Rate x Quantity.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountDiscount' => array (
                    'description' => 'Total discount for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountShipping' => array (
                    'description' => 'Total shipping for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountTax' => array (
                    'description' => 'Total taxes for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountFinal' => array (
                    'description' => 'Total taxes for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountPaid' => array (
                    'description' => 'Total amount paid.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountRefunded' => array (
                    'description' => 'Total amount refunded.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'sdata' => array (
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'idActualStatus' => array (
                    'description' => 'Actual status of the order. FK: commerce_taskstatus.tsid.',
                    'type' => 'int',
                    'length' => 4,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'idVisibleStatus' => array (
                    'description' => 'Visible status of the order. FK: commerce_taskstatus.tsid.',
                    'type' => 'int',
                    'length' => 4,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('oid'),
                ),
                'idActualStatus' => array (
                    'columns' => array ('idActualStatus'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__commerce_orderitem',
            'description' => 'Order item data.',
            'columns' => array (
                'oiid' => array (
                    'description' => 'Order item ID',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'oid' => array (
                    'description' => 'Order ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idProduct' => array (
                    'description' => 'Product ID. FK: cataog_product.pid',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'name' => array (
                    'description' => 'A heading for the cart item. Usually the product name.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'unitWeight' => array (
                    'description' => 'Unit weight.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'totalWeight' => array (
                    'description' => 'Total weight.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'rate' => array (
                    'description' => 'Unit price of the item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                ),
                'quantity' => array (
                    'description' => 'Number of units.',
                    'type' => 'int',
                    'length' => 10,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountProduct' => array (
                    'description' => 'Rate x Quantity.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountTax' => array (
                    'description' => 'Total taxes for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountDiscount' => array (
                    'description' => 'Total discount for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountShipping' => array (
                    'description' => 'Total shipping for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amountFinal' => array (
                    'description' => 'Final value for this item.',
                    'type' => 'decimal',
                    'length' => '20,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'sdata' => array (
                    'type' => 'blob',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'status' => array (
                    'description' => '1 = Active, 0 = Removed',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('oiid'),
                ),
                'oid' => array (
                    'columns' => array ('oid'),
                ),
                'idProduct' => array (
                    'columns' => array ('idProduct'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_shipment',
            'description' => 'Shipment data.',
            'columns' => array (
                'sid' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'scode' => array (
                    'description' => 'Shipment code.',
                    'type' => 'varchar',
                    'length' => 20,
                    'default' => NULL,
                ),
                'oid' => array (
                    'description' => 'Order ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'cid' => array (
                    'description' => 'Carrier ID. Usually same as order.idCarrier.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'description' => array (
                    'description' => 'Details about the shipment.',
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'idCreator' => array (
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned'),
                ),
                'idVerifier' => array (
                    'type' => 'int',
                    'length' => 20,
                    'default' => NULL,
                    'flags' => array ('unsigned'),
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtVerified' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                ),
                'status' => array (
                    'description' => '0 = Dispatched, 1 = Delivered, -1 = Failed.',
                    'type' => 'int',
                    'length' => 2,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('sid'),
                ),
                'scode' => array (
                    'columns' => array ('scode')
                ),
                'oid' => array (
                    'columns' => array ('oid')
                ),
            )
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_taskstatus',
            'description' => 'Task status data.',
            'columns' => array (
                'tsid' => array (
                    'description' => 'Task Status ID.',
                    'type' => 'int',
                    'length' => 4,
                    'default' => NULL,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'parentId' => array (
                    'description' => 'Parent ID.',
                    'type' => 'int',
                    'length' => 4,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'colorCode' => array (
                    'description' => 'Hex color code.',
                    'type' => 'varchar',
                    'length' => 8,
                    'default' => NULL,
                ),
                'isLocked' => array (
                    'description' => 'Whether the object is locked.',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                ),
                'status' => array (
                    'description' => '-1 = hidden, 0 = disabled, 1 = enabled',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tsid'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__commerce_taskstatus_i18n',
            'description' => 'Task status i18n data.',
            'columns' => array (
                'tsid' => array (
                    'description' => 'Task Status ID.',
                    'type' => 'int',
                    'length' => 4,
                    'default' => NULL,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'description' => 'Language ID.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tsid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_tax',
            'description' => 'Tax data.',
            'columns' => array (
                'tid' => array (
                    'description' => 'Tax ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'rate' => array (
                    'description' => 'Tax rate.',
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'status' => array (
                    'description' => '-1 = Deleted, 0 = Disabled, 1 = Enabled',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tid'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__commerce_tax_i18n',
            'description' => 'Tax i18n data.',
            'columns' => array (
                'tid' => array (
                    'description' => 'Tax ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'description' => 'Language ID.',
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
                    'columns' => array ('tid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_taxgroup',
            'description' => 'Tax group data.',
            'columns' => array (
                'tgid' => array (
                    'description' => 'Tax Group ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                'dtDeleted' => array (
                    'type' => 'datetime',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'status' => array (
                    'description' => '-1 = Deleted, 0 = Disabled, 1 = Enabled',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('tgid'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_taxrule',
            'description' => 'Tax rules as per tax group.',
            'columns' => array (
                'trid' => array (
                    'description' => 'Tax Rule ID.',
                    'type' => 'int',
                    'length' => 20,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'tgid' => array (
                    'description' => 'Tax Group ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'tid' => array (
                    'description' => 'Tax ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'description' => array (
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
                'ooa' => array (
                    'description' => 'Order of appearance',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'behavior' => array (
                    'description' => 'r = replace, c = combine, s = succeed.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'idCountry' => array (
                    'description' => 'Country ID',
                    'type' => 'int',
                    'length' => 10,
                    'default' => NULL,
                    'flags' => array ('nullable', 'unsigned'),
                ),
                'idState' => array (
                    'description' => 'State ID',
                    'type' => 'int',
                    'length' => 10,
                    'default' => NULL,
                    'flags' => array ('nullable', 'unsigned'),
                ),
                'dtCreated' => array (
                    'type' => 'datetime',
                ),
                
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('trid'),
                ),
                'tgid' => array (
                    'columns' => array ('tgid'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_carrier',
            'description' => 'Carrier data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Carrier ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned', 'increment'),
                ),
                'ctid' => array (
                    'description' => 'Carrier Type ID.',
                    'type' => 'varchar',
                    'length' => 64,
                ),
                'settings' => array (
                    'description' => 'Serialized settings.',
                    'type' => 'blob',
                ),
                'ooa' => array (
                    'description' => 'Order of appearance.',
                    'type' => 'int',
                    'length' => 10,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'isFree' => array (
                    'description' => 'Whether this is free shipping.',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                ),
                'status' => array (
                    'description' => '-1 = Deleted, 0 = Disabled, 1 = Enabled',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 1,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid'),
                ),
            ),
        ));
        $schema_helper->createTable(array (
            'name' => '%__commerce_carrier_i18n',
            'description' => 'Carrier i18n data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Carrier ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'ail' => array (
                    'description' => 'Language ID.',
                    'type' => 'varchar',
                    'length' => 8,
                ),
                'name' => array (
                    'type' => 'varchar',
                    'length' => 255,
                ),
                'description' => array (
                    'type' => 'text',
                    'default' => NULL,
                    'flags' => array ('nullable'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'ail'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_carrier_scope',
            'description' => 'Carrier scope.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Carrier ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'idCountry' => array (
                    'description' => 'Country ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'idState' => array (
                    'description' => 'State ID.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'idRegion' => array (
                    'description' => 'Region ID.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'status' => array (
                    'description' => '0 = disabled, 1 = partially enabled, 2 = fully enabled.',
                    'type' => 'int',
                    'length' => 2,
                    'default' => 0,
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'idCountry', 'idState', 'idRegion'),
                ),
                'cid' => array (
                    'columns' => array ('cid'),
                ),
            ),
        ));
        
        $schema_helper->createTable(array (
            'name' => '%__commerce_carrier_standard',
            'description' => 'Standard carrier rate data.',
            'columns' => array (
                'cid' => array (
                    'description' => 'Carrier ID.',
                    'type' => 'int',
                    'length' => 10,
                    'flags' => array ('unsigned'),
                ),
                'idCountry' => array (
                    'description' => 'Country ID.',
                    'type' => 'varchar',
                    'length' => 3,
                ),
                'idState' => array (
                    'description' => 'State ID.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'idRegion' => array (
                    'description' => 'Region ID.',
                    'type' => 'int',
                    'length' => 20,
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'basisOfCharge' => array (
                    'type' => 'varchar',
                    'length' => 16,
                    'default' => 'weight',
                ),
                'till' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
                'amount' => array (
                    'type' => 'decimal',
                    'length' => '10,3',
                    'default' => 0,
                    'flags' => array ('unsigned'),
                ),
            ),
            'indexes' => array (
                'primary' => array (
                    'columns' => array ('cid', 'idCountry', 'idState', 'idRegion', 'till'),
                ),
                'cid' => array (
                    'columns' => array ('cid'),
                ),
            ),
        ));
        
    }
    
    public static function uninstall() {
        
        $schema_helper = \Natty::getDbo()->getSchemaHelper();

        $schema_helper->dropTable('%__commerce_cartitem');
        
        $schema_helper->dropTable('%__commerce_order');
        $schema_helper->dropTable('%__commerce_orderitem');
        
        $schema_helper->dropTable('%__commerce_taskstatus');
        $schema_helper->dropTable('%__commerce_taskstatus_i18n');
        
        $schema_helper->dropTable('%__commerce_tax');
        $schema_helper->dropTable('%__commerce_tax_i18n');
        
        $schema_helper->dropTable('%__commerce_taxgroup');
        $schema_helper->dropTable('%__commerce_taxgroup_i18n');
        
        $schema_helper->dropTable('%__commerce_taxrule');
        
        $schema_helper->dropTable('%__commerce_carrier');
        $schema_helper->dropTable('%__commerce_carrier_i18n');
        $schema_helper->dropTable('%__commerce_carrier_scope');
        
        $schema_helper->dropTable('%__commerce_carrier_standard');
        
    }
    
    public static function enable() {
        
        if ( \Natty::readSetting('commerce--installing') ):
            
            // Create default statuses
            $tstatus_controller = \Natty::getHandler('commerce--taskstatus');
        
            $file = NATTY_ROOT . DS . \Natty::packagePath('module', 'commerce') . DS . 'data/taskstatus-coll.en-us.csv';
            $fp = fopen($file, 'r');
            while ($line = fgetcsv($fp)):

                if (!isset ($line_keys)):
                    $line_keys = $line;
                    continue;
                endif;

                $line = array_combine($line_keys, $line);
                $line['isNew'] = TRUE;
                $tstatus_controller->createAndSave($line);

            endwhile;
            
        endif;
        
        parent::enable();
        
    }
    
}