<?php

namespace Gsoft\Webpos\Version\V3\Model\Api;

use Gsoft\Webpos\Api\CatalogInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class Catalog implements CatalogInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory;
     */
    private $optionsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */

    protected $_stockItemRepository;

    private $attributeFactory;
    protected $optionLoader;
    /**
     * Order Payment
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Collection
     */
    protected $paymentCollection;


    /**
     * Payment Model Config
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/stock_threshold_qty';

    private $getStockItemConfiguration;
    private $productSalableQty;
    private $stockResolver;
    private $storeManager;
    protected $scopeConfig;
    protected $getSourceItemsBySku;
    protected $reservesFactory;

    protected $stockSourceFactory;
    protected $stockFactory;

    protected $reservedStock;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\ConfigurableProduct\Helper\Product\Options\Loader $optionLoader,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPayment,
        \Magento\Payment\Model\Config $paymentConfig,
        GetProductSalableQtyInterface $productSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $getSourceItemsBySku,
        \Gsoft\Webpos\Model\StockreservationFactory $reservationF,
        \Gsoft\Webpos\Model\Api\Data\StockFactory $stockFactory,
        \Gsoft\Webpos\Model\Api\Data\StocksourceFactory $stockSourceFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory = null
    )
    {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_stockItemRepository = $stockItemRepository;
        $this->optionLoader = $optionLoader;
        $this->attributeFactory = $attributeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);
        $this->paymentCollection = $orderPayment;

        $this->_paymentConfig = $paymentConfig;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->productSalableQty = $productSalableQty;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->reservesFactory = $reservationF;
        $this->stockSourceFactory=$stockSourceFactory;
        $this->stockFactory=$stockFactory;

    }

    /**
     * Get simples from configurable
     *
     * @api
     * @param string $sku
     * @return mixed[]
     */

    public function getSimples($sku, $store_id)
    {

        $product = $this->productRepository->get($sku);
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);
        $configurables = $productTypeInstance->getConfigurableAttributesAsArray($product);
        $attribute_configurable = [];
        //$store_id = $this->scopeConfig->getValue('webpos/general/store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //  if (empty($store_id)) $store_id = 1;
        $website_id = $this->storeManager->getStore($store_id)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($website_id)->getCode();

        $lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        foreach ($configurables as $id_at => $att) {
            $attribute_configurable[] = ['id' => $id_at, 'label' => $att['label'], 'code' => $att['attribute_code'], 'options' => $att['options']];
        }
        $lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $childrenList = [];
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];
            /*foreach($configurables as $attribute){

                $attrCode = $attribute['attribute_code'];
                $item=['code'=>$attribute['attribute_code'],'id'=>$attribute['attribute_id'],'label'=>$attribute['store_label'], 'value'=>$child->getData($attribute['attribute_code'])];
                $attributes['configurable_attributes'][]=$item;
            }*/
            foreach ($child->getAttributes() as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                if (null !== $value) {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();


            $salableQty = 0;

            $sourceItemsBySku = $this->getSourceItemsBySku->execute($child->getSku());
            $stock['sources'] = [];
            foreach ($sourceItemsBySku as $sourceItem) {
                $quantity = $sourceItem->getQuantity();
                $quantity -= $lowStock;
                $quantity-=$this->getReservedQty($child->getSku(),$sourceItem->getSourceCode());
                $stock['sources'][] = ["qty" => $quantity, "status" => $sourceItem->getStatus(), "source_code" => $sourceItem->getSourceCode(), "source_item_id" => $sourceItem->getSourceItemId()];
                if($sourceItem->getSourceCode() == $this->scopeConfig->getValue("webpos/general/source_stock'")){
                    $stock['qty']=$quantity;
                    $stock['status']=$sourceItem->getStatus();
                }
            }

            $attributes['webpos_stock'] = $stock;
            $attributes['price_without_tax'] = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount();
            $attributes['price'] = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getValue();
            $taxes = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getAdjustmentAmounts();
            if (is_array($taxes) && !empty($taxes['tax'])) $attributes['tax'] = $taxes['tax'];
            else $attributes['tax'] = 0;
            $childrenList[] = $attributes;

        }

        $exit['result'] = ['attributes' => $attribute_configurable, 'children' => $childrenList];

        return $exit;

    }

    function getReservedQty($sku, $source)
    {
        if ($this->reservedStock == null) {
            $this->reservedStock=[];
            $model = $this->reservesFactory->create();
            $collection = $model->getCollection();
            $collection->getSelect()->columns(['total' => new \Zend_Db_Expr('SUM(qty)')])->group("source");
            $col=$collection->getConnection()->fetchAll($collection->getSelect());

            foreach($collection as $p){
                $this->reservedStock[$p['source']][$p['sku']]=$p['total'];
            }
        }
        return empty($this->reservedStock[$source][$sku])?0:$this->reservedStock[$source][$sku];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductList($searchCriteria)
    {

        $list = $this->productRepository->getList($searchCriteria);
        $items = $list->getItems();
        $exit = [];
        // $website_id = $this->scopeConfig->getValue('webpos/general/website', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //if (empty($website_id)) $website_id = 1;
        // $websiteCode = $this->storeManager->getWebsite($website_id)->getCode();
        $lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($items as $k => $p) {
            $extensionAttributes = $p->getExtensionAttributes();

            $extensionAttributes->setData("webpos_price_without_tax", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount());

            $extensionAttributes->setData("webpos_price", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getValue());
            $taxes = $p->getPriceInfo()->getPrice("final_price")->getAmount()->getAdjustmentAmounts();
            if (is_array($taxes) && !empty($taxes['tax'])) $extensionAttributes->setData('webpos_tax', $taxes['tax']);
            else $extensionAttributes->setData('webpos_tax', 0);
            /* if ($this->_stockItemRepository->get($p->getId())->getIsInStock()) $qty = $this->_stockItemRepository->get($p->getId())->getQty();
             else $qty = 0;*/


            $sourceItemsBySku = $this->getSourceItemsBySku->execute($p->getSku());


            $stock=$this->stockFactory->create();

            $sources=null;

            foreach ($sourceItemsBySku as $sourceItem) {
                $source = $this->stockSourceFactory->create();
                $quantity = $sourceItem->getQuantity();
                $quantity -= $lowStock;
                $quantity-=$this->getReservedQty($p->getSku(),$sourceItem->getSourceCode());

                $source->setQty($quantity);
                $source->setStatus($sourceItem->getStatus());
                $source->setSourceCode($sourceItem->getSourceCode());
                $source->setSourceId($sourceItem->getSourceItemId());
                $sources[]=$source;
                if($sourceItem->getSourceCode() == $this->scopeConfig->getValue("webpos/general/source_stock'")) {
                    $stock->setQty($quantity);
                    $stock->setStatus($sourceItem->getStatus());
                }
            }
            $stock->setSources($sources);

            $extensionAttributes->setData("webpos_stock", $stock);
            $p->setExtensionAttributes($extensionAttributes);
            $exit[] = $p;
        }
        $list->setItems($items);
        return $list;
    }



}