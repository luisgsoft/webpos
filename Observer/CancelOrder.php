<?php
namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class CancelOrder implements ObserverInterface
{



    private $resource;
    protected $orderPaymentFactory;
    protected $collectionPaymentFactory;
    protected $timezoneInterface;
    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Gsoft\Webpos\Model\OrderPaymentFactory                      $orderPaymentFactory,
        \Gsoft\Webpos\Model\ResourceModel\OrderPayment\CollectionFactory                      $collectionPaymentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {

        $this->resource=$resource;
        $this->orderPaymentFactory=$orderPaymentFactory;
        $this->collectionPaymentFactory=$collectionPaymentFactory;
        $this->timezoneInterface=$timezoneInterface;
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

        if(!empty($order->getData("webpos_terminal"))) {
            $payments=$this->collectionPaymentFactory->create()->addFieldToFilter("order_id", $order->getId());
            if(!empty($payments)) {
                foreach ($payments as $payment) {
                    $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
                    $payment_array = ['code' => $payment->getCode(), 'label' => $payment->getLabel(), 'delivered' => $payment->getDelivered() * -1, 'reference' => $payment->getReference(), 'name' => $payment->getName(), 'amount' => $payment->getAmount() * -1];
                    $webpospayment = $this->orderPaymentFactory->create();
                    $webpospayment->setData($payment_array);
                    $webpospayment->setData("order_id", $order->getId());
                    $webpospayment->setData("terminal", $order->getData("webpos_terminal"));
                    $webpospayment->setData("user", $order->getData("webpos_user"));
                    $webpospayment->setData("increment_id", $order->getIncrementId());
                    $webpospayment->setData("created_at", $dateTime);
                    $webpospayment->save();
                }
            }

        }
    }

}
