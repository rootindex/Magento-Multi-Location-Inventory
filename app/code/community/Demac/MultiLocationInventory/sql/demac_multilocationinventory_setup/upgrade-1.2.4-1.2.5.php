<?php
$installer = $this;

$installer->startSetup();

/**
 * Add min_qty to table 'demac_multilocationinventory/location'
 */
$table = $installer->getTable('demac_multilocationinventory/location');

$installer->getConnection()
    ->addColumn($table, 'min_qty',
                array(
                    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'comment'  => 'Min Qty before marked out of stock',
                    'default'  => '0',
                    'after'    => 'external_id'
                )
    );
$installer->endSetup();
