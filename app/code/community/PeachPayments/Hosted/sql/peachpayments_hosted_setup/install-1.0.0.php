<?php
/**
 * Copyright (c) 2019 Peach Payments. All rights reserved. Developed by Francois Raubenheimer
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getConnection()
    ->newTable($this->getTable('peachpayments_hosted/web_hooks'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ],
        'Identifier'
    )
    ->addColumn(
        'amount',
        Varien_Db_Ddl_Table::TYPE_DECIMAL,
        '10,4',
        [
            'default' => 0.0000
        ],
        'Amount payable'
    )
    ->addColumn(
        'currency',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        3,
        [],
        'Currency'
    )
    ->addColumn(
        'checkout_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        32,
        [],
        'Checkout identifier'
    )
    ->addColumn(
        'peach_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        32,
        [],
        'Peach Identifier'
    )
    ->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
        ],
        'Order identifier'
    )
    ->addColumn(
        'order_increment_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        50,
        [],
        'Order increment identifier'
    )
    ->addColumn(
        'merchant_name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        120,
        [],
        'Merchant name'
    )
    ->addColumn(
        'merchant_transaction_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [],
        'Merchant transaction id'
    )
    ->addColumn(
        'payment_brand',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        32,
        [],
        'Payment brand'
    )
    ->addColumn(
        'payment_type',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        8,
        [],
        'Payment type'
    )
    ->addColumn(
        'result_code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        12,
        [],
        'Result Code'
    )
    ->addColumn(
        'result_description',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        [],
        'Result description'
    )
    ->addColumn(
        'signature',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        64,
        [],
        'Signature'
    )
    ->addColumn(
        'timestamp',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        [],
        'Timestamp from peach'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        [],
        'Created At Time'
    )
    ->setComment('PeachPayments Hosted table');

$this->getConnection()
    ->createTable($table);

$installer->endSetup();
