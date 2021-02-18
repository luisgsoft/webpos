<?php
namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class CancelOrder implements ObserverInterface
{



    private $resource;


    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {

        $this->resource=$resource;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        $sql="Delete from webpos_stock_reservation where order_id=".$order->getId()." AND accepted=0";
        $this->resource->getConnection()->query($sql);

        $sql="update webpos_stock_reservation set canceled=1 where order_id=".$order->getId();
        $this->resource->getConnection()->query($sql);
    }

}
