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
    protected $couponModel;
    protected $ruleRepository;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection                        $resource,
        \Gsoft\Webpos\Model\OrderPaymentFactory                          $orderPaymentFactory,
        \Gsoft\Webpos\Model\ResourceModel\OrderPayment\CollectionFactory $collectionPaymentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface             $timezoneInterface,
        \Magento\SalesRule\Model\Coupon                                  $couponModel,
        \Magento\SalesRule\Api\RuleRepositoryInterface                   $ruleRepository
    )
    {

        $this->resource = $resource;
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->collectionPaymentFactory = $collectionPaymentFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->couponModel = $couponModel;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        $sql = "Delete from webpos_stock_reservation where order_id=" . $order->getId() . " AND accepted=0";
        $this->resource->getConnection()->query($sql);

        $sql = "update webpos_stock_reservation set canceled=1 where order_id=" . $order->getId();
        $this->resource->getConnection()->query($sql);

        if (!empty($order->getData("webpos_terminal"))) {
            $payments = $this->collectionPaymentFactory->create()->addFieldToFilter("order_id", $order->getId());
            if (!empty($payments)) {
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

        $coupon = $order->getCouponCode();
        if (!empty($coupon) && !empty($order->getDiscountAmount())) {

            $this->updateCouponAmount($coupon, $order);
        }


    }

    protected function updateCouponAmount($couponCode, $order)
    {

        try {
            $ruleId = $this->couponModel->loadByCode($couponCode)->getRuleId();
            $rule = $this->ruleRepository->getById($ruleId);
            if ($rule->getDescription() != "giftcard") return;
            $total = $rule->getDiscountAmount();

            $remainDiscount = $total + abs($order->getDiscountAmount());

            if (!empty($remainDiscount) && $remainDiscount > 0) {
                $rule->setDiscountAmount($remainDiscount);
                $rule->setUsesPerCoupon($rule->getUsesPerCoupon() - 1);
                $rule->setUsesPerCustomer($rule->getUsesPerCustomer() - 1);
                $rule->setIsActive(1);
                $this->ruleRepository->save($rule);
            } 

        } catch (\Exception $e) {

        }
    }

}
