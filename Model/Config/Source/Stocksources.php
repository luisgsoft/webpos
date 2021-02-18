<?php

namespace Gsoft\Webpos\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class Stocksources extends \Magento\Framework\DataObject
    implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_appConfigScopeConfigInterface;
    protected $sources;
    protected $productMetadata;

    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata

    )
    {
        $this->productMetadata = $productMetadata;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;


    }

    public function toOptionArray()
    {
        if (version_compare($this->productMetadata->getVersion(), "2.3", ">=")) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $sources = $objectManager->get('\Magento\Inventory\Model\ResourceModel\Source\Collection');

            $sourceListArr = $sources->load();
            $methods = array();
            foreach ($sourceListArr as $sourceItemName) {
                $sourceCode = $sourceItemName->getSourceCode();
                $sourceName = $sourceItemName->getName();


                $methods[$sourceCode] = array(
                    'label' => $sourceName,
                    'value' => $sourceCode
                );
            }
            return $methods;
        }else return [];
    }
}