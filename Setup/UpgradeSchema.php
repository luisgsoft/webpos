<?php

namespace Gsoft\Webpos\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{


    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            $connection = $installer->getConnection();

            $connection->addColumn(
                $installer->getTable('sales_order_grid'),
                'webpos_user',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Webpos Seller'
                ]
            );
            $connection->addColumn(
                $installer->getTable('sales_order_grid'),
                'webpos_terminal',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Webpos Terminal'
                ]
            );

        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {

            $connection = $installer->getConnection();


            $connection->addColumn($installer->getTable('quote'), 'webpos_terminal', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' => 50,
                'nullable' => true,
                'comment' => 'Terminal',
            ]);
            $connection->addColumn($installer->getTable('sales_order'), 'webpos_terminal', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'LENGTH' => 50,
                'comment' => 'Terminal',
            ]);


        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {

            $connection = $installer->getConnection();


            $connection->addColumn($installer->getTable('sales_creditmemo'), 'webpos_terminal', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' => 50,
                'nullable' => true,
                'comment' => 'Terminal',
            ]);
            $connection->addColumn($installer->getTable('sales_creditmemo'), 'webpos_payment', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Forma de pago',
            ]);


        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {

            $connection = $installer->getConnection();
            $connection->addColumn($installer->getTable('quote'), 'webpos_discount_fixed', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'comment' => 'Descuento fijo',
                'nullable' => true,

            ]);
            $connection->addColumn($installer->getTable('quote'), 'webpos_discount_percent', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'comment' => 'Descuento porcentual',
                'nullable' => true,

            ]);
            $connection->addColumn($installer->getTable('sales_order'), 'webpos_discount_fixed', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'comment' => 'Descuento fijo',
                'nullable' => true,

            ]);
            $connection->addColumn($installer->getTable('sales_order'), 'webpos_discount_percent', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'comment' => 'Descuento porcentual',
                'nullable' => true,

            ]);


        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {

            $connection = $installer->getConnection();
            $connection->addColumn($installer->getTable('quote'), 'webpos_discount_label', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Detalle Descuento',
                'nullable' => true,

            ]);

            $connection->addColumn($installer->getTable('sales_order'), 'webpos_discount_label', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Detalle Descuento fijo',
                'nullable' => true,

            ]);


        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $connection = $installer->getConnection();
            $connection->query('CREATE TABLE `webpos_stock_reservation` (
                  `id` int(11) NOT NULL,
                  `order_id` int(10) UNSIGNED NOT NULL,
                  `item_id` int(11) UNSIGNED NOT NULL,
                  `sku` varchar(250) NOT NULL,
                  `qty` int(11) NOT NULL,
                  `source` varchar(255) NOT NULL,
                  `created_at` datetime NOT NULL,
                  `updated_at` datetime NOT NULL,
                  `accepted` tinyint(4) NOT NULL,
                  `accepted_user` varchar(150) DEFAULT NULL,
                  `accepted_at` datetime DEFAULT NULL,
                  `canceled` tinyint(1) NOT NULL,
                  `shipped` tinyint(4) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
            $connection->query('ALTER TABLE `webpos_stock_reservation`
              ADD PRIMARY KEY (`id`),
              ADD KEY `order_id` (`order_id`),
              ADD KEY `item_id` (`item_id`);');
            $connection->query('ALTER TABLE `webpos_stock_reservation`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
            $connection->query('ALTER TABLE `webpos_stock_reservation`  ADD CONSTRAINT `webpos_stock_reservation_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_order` (`entity_id`),  ADD CONSTRAINT `webpos_stock_reservation_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `sales_order_item` (`item_id`);');
        }
        if (version_compare($context->getVersion(), '1.0.10', '<')) {

            $connection = $installer->getConnection();
            $connection->addColumn($installer->getTable('quote'), 'webpos_alias', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Webpos',
                'nullable' => true,

            ]);

            $connection->addColumn($installer->getTable('sales_order'), 'webpos_alias', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Webpos',
                'nullable' => true,

            ]);


        }
        if (version_compare($context->getVersion(), '1.0.11', '<')) {

            $connection = $installer->getConnection();
            $connection->addColumn($installer->getTable('quote'), 'webpos_user', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Webpos user',
                'nullable' => true,

            ]);

            $connection->addColumn($installer->getTable('sales_order'), 'webpos_user', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Webpos user',
                'nullable' => true,

            ]);


        }
        $installer->endSetup();
    }
}
