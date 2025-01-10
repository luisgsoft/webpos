<?php
namespace Gsoft\Webpos\Version\V3\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveOrder
{



    private $getStockItemConfiguration;

    private $stockResolver;
    private $scopeConfig;
    protected $getSourceItemsBySku;
    protected $reservesFactory;
    protected $couponModel;
    protected $ruleRepository;


    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockResolverInterface $stockResolver,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $getSourceItemsBySku,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Gsoft\Webpos\Model\StockreservationFactory $reservationF,
        \Magento\SalesRule\Model\Coupon                              $couponModel,
        \Magento\SalesRule\Api\RuleRepositoryInterface               $ruleRepository


    ) {

        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockResolver = $stockResolver;
        $this->scopeConfig = $scopeConfig;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->reservesFactory=$reservationF;
        $this->couponModel = $couponModel;
        $this->ruleRepository = $ruleRepository;


    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->getValue("webpos/general/enabled")) return;
        $order = $observer->getEvent()->getData('order');
        $terminal=$order->getData("webpos_terminal");

        if(empty($terminal)) {
            $coupon = $order->getCouponCode();
            if (!empty($coupon) && !empty($order->getDiscountAmount())) {

                $this->updateCouponAmount($coupon, $order);
            }
        }


        if($this->scopeConfig->getValue("webpos/general/disallow_tracking")) return;
        $reservations=[];
        /* @var \Magento\Sales\Model\Order $order */


        if(!empty($terminal)) return;
        //if(!empty($order->getData("webpos_terminal"))) return;
        /**@var \Magento\Sales\Model\Order\Item $child*/
        foreach($order->getAllVisibleItems() as $child) {


            $stocks = $this->getQuantities($child->getSku());
            $pending=$child->getQtyOrdered();
            foreach($stocks as $code=>$wh){
               if($wh >= $pending){
                   $reservations[]=['item'=>$child, 'qty'=>$pending, 'source'=>$code];
                   break;
               }else{

                   $reservations[]=['item'=>$child, 'qty'=>$wh, 'source'=>$code];
                   $pending-=$wh;
               }
            }
        }

        foreach($reservations as $reserve){
         //   for($i=0;$i<$reserve['qty'];$i++) {
                $r = $this->reservesFactory->create();
                $r->setData("order_id", $order->getId());
                $r->setData("item_id", $reserve['item']->getId());
                $r->setData("sku", $reserve['item']->getSku());
                $r->setData("qty", $reserve['qty']);
                $r->setData("source", $reserve['source']);
                $r->setData("created_at", time());
                $r->setData("updated_at", time());
                $r->save();
           // }
        }


    }
    function getQuantities($sku){
        $sourceItemsBySku = $this->getSourceItemsBySku->execute($sku);
        $stock = [];
        foreach ($sourceItemsBySku as $sourceItem) {
            $qty=$sourceItem->getQuantity();
            $model=$this->reservesFactory->create();
            $collection = $model->getCollection()->addFieldToFilter("source", $sourceItem->getSourceCode())->addFieldToFilter("sku", $sku);

            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['total' => new \Zend_Db_Expr('SUM(qty)')]);
            /**@var \Gsoft\Webpos\Model\ResourceModel\Stockreservation\Collection $collection*/
            $qty_reserved=$collection->getConnection()->fetchOne($collection->getSelect());
            if(empty($qty_reserved)) $qty_reserved=0;

            $stock[$sourceItem->getSourceCode()]=$qty-$qty_reserved;
        }

        arsort($stock);
        return $stock;
    }

    protected function updateCouponAmount($couponCode, $order)
    {

        try {
            $ruleId = $this->couponModel->loadByCode($couponCode)->getRuleId();
            $rule = $this->ruleRepository->getById($ruleId);
            if ($rule->getDescription() != "giftcard") return;
            $total = $rule->getDiscountAmount();

            $remainDiscount = $total - abs($order->getDiscountAmount());

            if(!empty($remainDiscount) && $remainDiscount > 0) {
                $rule->setDiscountAmount($remainDiscount);
                $rule->setUsesPerCoupon($rule->getUsesPerCoupon() + 1);
                $rule->setUsesPerCustomer($rule->getUsesPerCustomer() + 1);
                $this->ruleRepository->save($rule);
            }else{
                $rule->setIsActive(0);
               // $rule->setDiscountAmount(0);
                $this->ruleRepository->save($rule);
            }

        } catch (\Exception $e) {

        }
    }

}
