<?php

namespace Gsoft\Webpos\Cron;


class Stock
{

    private $scopeConfig;
    protected $_logger;
    protected $connection;
    protected $indexerFactory;

    public function __construct(

        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Gsoft\Webpos\Logger\Logger $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    )
    {

        $this->scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->connection = $resource->getConnection();
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue("webpos/general/is_in_stock")) return;
        try {
            $sql = "update cataloginventory_stock_item set is_in_stock=1 where qty > 0";
            $this->connection->query($sql);
            $sql = "update inventory_source_item set status=1 where quantity > 0";
            $this->connection->query($sql);
            $sql = "update cataloginventory_stock_status set stock_status=1 where qty > 0";
            $this->connection->query($sql);
            $indexer = $this->indexerFactory->create();
            $indexer->load("cataloginventory_stock");
            $indexer->reindexAll();

        } catch (\Exception $e) {
            $this->_logger->info("Cron stock");
            $this->_logger->info($e->getMessage());
        }


    }


}
