<?php

namespace Gsoft\Webpos\Version\V3\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveShipment implements ObserverInterface
{


    private $getsource;

    private $resource;
    private $scopeConfig;
    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId $GetSourceCodeByShipmentId,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->getsource = $GetSourceCodeByShipmentId;
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if($this->scopeConfig->getValue("webpos/general/disallow_tracking")) return;
        /* @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getData('shipment');
        /* @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();

        //if(!empty($order->getData("webpos_terminal"))) return;
        $source=$shipment->getExtensionAttributes()->getSourceCode();

        if (empty($source)) return;
        try {
            /**@var \Magento\Sales\Model\Order\Shipment\Item $child */
            foreach ($shipment->getAllItems() as $child) {

                $sql = "select sum(qty) from webpos_stock_reservation where item_id=" . $child->getOrderItemId() . " AND accepted=1 AND source=" . $this->resource->getConnection()->quote($source);

                $total = $this->resource->getConnection()->fetchOne($sql);

                if ($total >= $child->getQty()) {
                    //todas las unidades se han apartado
                    $this->resource->getConnection()->query("Delete from webpos_stock_reservation where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId() . " AND source=" . $this->resource->getConnection()->quote($source) . " AND accepted=1 limit " . $child->getQty());
                } else {

                    //faltan unidades por apartar, hay que avisar a la tienda
                    $pending = $child->getQty();
                  /*  if ($total > 0) {
                        //borro las que sí están apartadas por la tienda
                        $this->resource->getConnection()->query("Delete from webpos_stock_reservation where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId() . " AND source=" . $this->resource->getConnection()->quote($source) . " AND accepted=1 limit " . $child->getQty());
                        $pending -= $total;
                    }*/
                    //marco como enviadas las que no se han apartado, para que la tienda se entere
                    $sql = "update webpos_stock_reservation  set shipped=shipped+".$child->getQty()." where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId();

                    $this->resource->getConnection()->query($sql);


                }

            }
        }catch(Exception $e){
            throw $e;
        }


    }

}
