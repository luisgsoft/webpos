<?php
namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveOrder implements ObserverInterface
{



    private $getStockItemConfiguration;

    private $stockResolver;
    private $scopeConfig;
    protected $getSourceItemsBySku;
    protected $reservesFactory;
    protected $couponModel;
    protected $ruleRepository;
    protected $eventManager;


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
        \Magento\SalesRule\Api\RuleRepositoryInterface               $ruleRepository,
        ?\Magento\Framework\Event\ManagerInterface $eventManager = null


    ) {

        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockResolver = $stockResolver;
        $this->scopeConfig = $scopeConfig;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->reservesFactory=$reservationF;
        $this->couponModel = $couponModel;
        $this->ruleRepository = $ruleRepository;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Event\ManagerInterface::class);


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
            // Otros módulos pueden cambiar el stock de cada almacén (código => disponible) con el que se reparte la
            // reserva (ej: Gsoft_AdvancedStock, cuyo stock no va por almacén, lo reserva todo en default_source)
            $transport = new \Magento\Framework\DataObject([
                'stocks' => $stocks,
                'default_source' => (string)$this->scopeConfig->getValue("webpos/general/source_stock"),
            ]);
            $this->eventManager->dispatch('gsoft_webpos_before_stock_reservation_sources', [
                'transport' => $transport,
                'order_item' => $child,
                'order' => $order,
            ]);
            $stocks = is_array($transport->getData('stocks')) ? $transport->getData('stocks') : [];
            if (empty($stocks)) continue;
            $pending = (float)$child->getQtyOrdered();
            $allocations = [];
            foreach ($stocks as $code => $wh) {
                if ($pending <= 0) break;
                if ($wh <= 0) continue;
                $qty = round(min((float)$wh, $pending), 4);
                $allocations[$code] = $qty;
                $pending = round($pending - $qty, 4);
            }
            // Si no hay stock suficiente en los almacenes (o Magento no gestiona el stock del producto, ej: los
            // rollos de Gsoft_AdvancedStock), lo que falta se reserva en el almacén con más stock
            if ($pending > 0) {
                $code = array_key_first($stocks);
                $allocations[$code] = round(($allocations[$code] ?? 0) + $pending, 4);
            }
            foreach ($allocations as $code => $qty) {
                $reservations[] = ['item' => $child, 'qty' => $qty, 'source' => $code];
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
                // Otros módulos pueden completar la reserva (ej: unit, la unidad de medida; vacía = unidades)
                $this->eventManager->dispatch('gsoft_webpos_before_save_stock_reservation', [
                    'reservation' => $r,
                    'order_item' => $reserve['item'],
                    'order' => $order,
                ]);
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
