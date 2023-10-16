<?php

namespace Gsoft\Webpos\Plugin\Api;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class OrderRepository
{


    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    protected $orderFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory, \Magento\Sales\Model\OrderFactory $orderFactory)
    {
        $this->extensionFactory = $extensionFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Add "delivery_type" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $terminal = $order->getData("webpos_terminal");
        $installments = $order->getData("webpos_installments");
        $booking = $order->getData("webpos_booking");
        $o = $this->orderFactory->create()->load($order->getId());

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setWebposTerminal($terminal);
        $extensionAttributes->setWebposInstallments($installments);
        $extensionAttributes->setWebposBooking($booking);
        $extensionAttributes->setCanCancel($o->canCancel());
        $extensionAttributes->setCanRefund($o->canCreditmemo());
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Add "delivery_type" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $terminal = $order->getData("webpos_terminal");
            $booking = $order->getData("webpos_booking");
            $installments = $order->getData("webpos_installments");
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setWebposTerminal($terminal);
            $extensionAttributes->setWebposBooking($booking);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }

}
