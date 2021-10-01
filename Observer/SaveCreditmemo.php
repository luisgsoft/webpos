<?php

namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveCreditmemo implements ObserverInterface
{


    private $getsource;

    private $resource;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**@var \Magento\Sales\Model\Order\Creditmemo $oCreditMemo*/
        $oCreditMemo = $oEvent->getCreditmemo();
        if ($oCreditMemo){
            /**
             * Allow credit memo with zero amount
             */
            $oCreditMemo->setAllowZeroGrandTotal(true);
        }

    }

}
