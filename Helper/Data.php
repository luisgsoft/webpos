<?php

namespace Gsoft\Webpos\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $objectManager;
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        parent::__construct($context);
        $this->objectManager = $objectmanager;
        $this->productMetadata = $productMetadata;

    }
    public function isVersionGreatherOrEqual($version){
        if (version_compare($this->productMetadata->getVersion(), $version, ">=")) return true;
        else return false;
    }
    public function loadObject($model){
        return $this->objectManager->get($model);
    }
}