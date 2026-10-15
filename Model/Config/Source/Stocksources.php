<?php

namespace Gsoft\Webpos\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;

class Stocksources extends \Magento\Framework\DataObject
    implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_appConfigScopeConfigInterface;
    protected $sourceCollectionFactory;

    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        SourceCollectionFactory $sourceCollectionFactory
    )
    {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    public function toOptionArray()
    {
        $methods = array();
        foreach ($this->sourceCollectionFactory->create() as $sourceItemName) {
            $sourceCode = $sourceItemName->getSourceCode();
            $sourceName = $sourceItemName->getName();

            $methods[$sourceCode] = array(
                'label' => $sourceName,
                'value' => $sourceCode
            );
        }
        return $methods;
    }
}
