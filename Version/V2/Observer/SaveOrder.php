<?php

namespace Gsoft\Webpos\Version\V2\Observer;

class SaveOrder
{

    private $scopeConfig;
    protected $reservesFactory;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Gsoft\Webpos\Model\StockreservationFactory $reservationF
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->reservesFactory = $reservationF;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $reservations = [];
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        $terminal = $order->getData("webpos_terminal");
        if (!empty($terminal)) return;
        //if(!empty($order->getData("webpos_terminal"))) return;
        /**@var \Magento\Sales\Model\Order\Item $child */
        foreach ($order->getAllVisibleItems() as $child) {

            $reservations[] = ['item' => $child, 'qty' => $child->getQtyOrdered(), 'source' => ''];

        }

        foreach ($reservations as $reserve) {
            for ($i = 0; $i < $reserve['qty']; $i++) {
                $r = $this->reservesFactory->create();
                $r->setData("order_id", $order->getId());
                $r->setData("item_id", $reserve['item']->getId());
                $r->setData("sku", $reserve['item']->getSku());
                $r->setData("qty", 1);
                $r->setData("source", $reserve['source']);
                $r->setData("created_at", time());
                $r->setData("updated_at", time());
                $r->save();
            }
        }


    }

}
