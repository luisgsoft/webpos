<?php

namespace Gsoft\Webpos\Model\ResourceModel\Invoice\Grid;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as OriginalCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Psr\Log\LoggerInterface as Logger;


/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    public function __construct(
        EntityFactory     $entityFactory,
        Logger            $logger,
        FetchStrategy     $fetchStrategy,
        EventManager      $eventManager,
                          $mainTable = 'sales_invoice_grid',
                          $resourceModel = Invoice::class,
        TimezoneInterface $timeZone = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
    }

    protected function _renderFiltersBefore()
    {

        $this->addFieldToFilter("main_table.webpos_booking", ["null" => true]);
        parent::_renderFiltersBefore();
    }
}
