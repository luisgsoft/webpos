<?php

namespace Gsoft\Webpos\Version\V2\Model\Api;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
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


    private $storeManager;
    protected $scopeConfig;
    protected $getSourceItemsBySku;
    protected $reservesFactory;

    protected $reservedStock;
    protected $stockRegistry;
    protected $stockSourceFactory;
    protected $stockFactory;

    public function __construct(

        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\ConfigurableProduct\Helper\Product\Options\Loader $optionLoader,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $orderPayment,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Gsoft\Webpos\Model\StockreservationFactory $reservationF,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Gsoft\Webpos\Model\Api\Data\StockFactory $stockFactory,
        \Gsoft\Webpos\Model\Api\Data\StocksourceFactory $stockSourceFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory = null
    )
    {
        parent::__construct($context);
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
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->reservesFactory = $reservationF;
        $this->stockRegistry=$stockRegistry;
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

            $_productStock= $this->stockRegistry->getStockItem($child->getId());

            $attributes['webpos_stock'] = ['qty'=>$_productStock->getQty(),'status'=>$_productStock->getIsInStock(), 'sources'=>null];





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
        /**@var \Magento\Framework\Api\SearchCriteria $searchCriteria*/
        $filters = $searchCriteria->getFilterGroups();
        foreach($filters as $g=>$group){
            foreach($group->getFilters() as $k=>$filter){
                if($filter->getField()=="store_id"){
                    $filter->setField("store");

                }
            }
        }

        $list = $this->productRepository->getList($searchCriteria);
        $items = $list->getItems();
        $exit = [];

        //$lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($items as $k => $p) {
            $configurable_product_options=null;
            if($p->getTypeId()=="configurable"){
                $data = $p->getTypeInstance()->getConfigurableOptions($p);

                $productAttributeOptions = $p->getTypeInstance(true)->getConfigurableAttributesAsArray($p);
                $atributos = array();
                foreach ($productAttributeOptions as $productAttribute) {

                    $atributos[$productAttribute['attribute_code']]=['id'=>$productAttribute['attribute_id'], 'label'=>$productAttribute['store_label']];
                }

                foreach($data as $opcion_group){
                    foreach($opcion_group as $opcion) {
                        $configurable_product_options[] = ['attribute_id' => $atributos[$opcion['attribute_code']]['id'], 'label' => $atributos[$opcion['attribute_code']]['label']];
                    }
                }


            }
            $extensionAttributes = $p->getExtensionAttributes();
            if(!empty($configurable_product_options)) $extensionAttributes->setData("configurable_product_options", $configurable_product_options);
            $extensionAttributes->setData("webpos_price_without_tax", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount());

            $extensionAttributes->setData("webpos_price", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getValue());
            $taxes = $p->getPriceInfo()->getPrice("final_price")->getAmount()->getAdjustmentAmounts();
            if (is_array($taxes) && !empty($taxes['tax'])) $extensionAttributes->setData('webpos_tax', $taxes['tax']);
            else $extensionAttributes->setData('webpos_tax', 0);

            $_productStock= $this->stockRegistry->getStockItem($p->getId());
            $stock=$this->stockFactory->create();
            $stock->setQty($_productStock->getQty());
            $stock->setStatus($_productStock->getIsInStock());
            $stock->setSources(null);
            $extensionAttributes->setData('webpos_stock', $stock);

            $p->setExtensionAttributes($extensionAttributes);
            $exit[] = $p;
        }
        $list->setItems($items);
        return $list;
    }



}