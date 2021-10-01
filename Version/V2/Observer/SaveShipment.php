<?php

namespace Gsoft\Webpos\Version\V2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;



class SaveShipment implements ObserverInterface
{

    private $resource;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->resource = $resource;


    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {


        /* @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getData('shipment');
        /* @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();

        //if(!empty($order->getData("webpos_terminal"))) return;


        try {
            /**@var \Magento\Sales\Model\Order\Shipment\Item $child */
            foreach ($shipment->getAllItems() as $child) {

                $sql = "select count(*) from webpos_stock_reservation where item_id=" . $child->getOrderItemId() . " AND accepted=1 ";

                $total = $this->resource->getConnection()->fetchOne($sql);

                if ($total >= $child->getQty()) {
                    //todas las unidades se han apartado
                    $this->resource->getConnection()->query("Delete from webpos_stock_reservation where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId() ." AND accepted=1 limit " . $child->getQty());
                } else {
                    //faltan unidades por apartar, hay que avisar a la tienda
                    $pending = $child->getQty();
                    if ($total > 0) {
                        //borro las que sí están apartadas por la tienda
                        $this->resource->getConnection()->query("Delete from webpos_stock_reservation where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId() . "  AND accepted=1 limit " . $child->getQty());
                        $pending -= $total;
                    }
                    //marco como enviadas las que no se han apartado, para que la tienda se entere
                    $sql = "update webpos_stock_reservation  set shipped=1 where order_id=" . $order->getId() . " AND item_id=" . $child->getOrderItemId() . " limit " . $pending;

                    $this->resource->getConnection()->query($sql);


                }

            }
        }catch(Exception $e){
            throw $e;
        }


    }

}

