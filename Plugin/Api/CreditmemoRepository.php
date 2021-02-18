<?php

namespace Gsoft\Webpos\Plugin\Api;

use Magento\Sales\Api\Data\CreditmemoExtensionInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;


class CreditmemoRepository
{



    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(CreditmemoExtensionInterfaceFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add "delivery_type" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(CreditmemoRepositoryInterface $subject, CreditmemoInterface $order)
    {

        $terminal = $order->getData("webpos_terminal");
        $payment = $order->getData("webpos_payment");
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

        $extensionAttributes->setWebposTerminal($terminal);
        $extensionAttributes->setWebposPayment($payment);
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
    public function afterGetList(CreditmemoRepositoryInterface $subject, CreditmemoSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $terminal = $order->getData("webpos_terminal");
            $payment = $order->getData("webpos_payment");
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setWebposTerminal($terminal);
            $extensionAttributes->setWebposPayment($payment);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }



}