<?php

namespace Gsoft\Webpos\Model\Api;

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
    protected $logger;

    /**
     * Payment Model Config
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/min_qty';

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
    protected $getReservationsQuantity;
    protected $sourceRepository;
    protected $eventManager;
    protected $stockRegistry;

    /**
     * Nombres de los almacenes por código (caché de la petición)
     *
     * @var array
     */
    private $sourceLabels = [];

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
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface $getReservationsQuantity,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository,
        \Psr\Log\LoggerInterface $logger,
        ?\Magento\Framework\Event\ManagerInterface $eventManager = null,
        ?\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry = null
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
        $this->getReservationsQuantity=$getReservationsQuantity;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->stockRegistry = $stockRegistry ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
    }

    /**
     * Datos de venta comunes a todos los productos, que otros módulos pueden cambiar por evento:
     * - webpos_unit: unidad de medida del precio y la cantidad (null = unidades normales).
     * - webpos_min_qty, webpos_max_qty, webpos_qty_increments (null si no hay incrementos): límites de cantidad en
     *   el carrito según la configuración de stock de Magento.
     * - webpos_stock_editable: si el TPV puede cambiar el stock del producto (false si lo gestiona otro módulo).
     * El stock disponible va en webpos_stock (qty).
     *
     * @param int $productId
     * @return array
     */
    private function getCartQtyLimits($productId)
    {
        $limits = [
            'webpos_unit' => null,
            'webpos_min_qty' => 1.0,
            'webpos_max_qty' => null,
            'webpos_qty_increments' => null,
            'webpos_stock_editable' => true,
        ];
        try {
            $stockItem = $this->stockRegistry->getStockItem((int)$productId);
            $min = (float)$stockItem->getMinSaleQty();
            $max = (float)$stockItem->getMaxSaleQty();
            $increments = $stockItem->getEnableQtyIncrements() ? (float)$stockItem->getQtyIncrements() : 0.0;
            $limits['webpos_min_qty'] = $min > 0 ? $min : 1.0;
            $limits['webpos_max_qty'] = $max > 0 ? $max : null;
            $limits['webpos_qty_increments'] = $increments > 0 ? $increments : null;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }

        return $limits;
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

        foreach ($configurables as $id_at => $att) {
            $attribute_configurable[] = ['id' => $id_at, 'label' => $att['store_label'] ?: ($att['frontend_label'] ?: $att['label']), 'code' => $att['attribute_code'], 'options' => $att['options']];
        }
        $lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $childrenList = [];
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];

            foreach ($child->getAttributes() as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                if (null !== $value) {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();

            $sourceItemsBySku = $this->getSourceItemsBySku->execute($child->getSku());
            $stock['sources'] = [];
            foreach ($sourceItemsBySku as $sourceItem) {
                $quantity = $this->getQty($child->getSku(), $sourceItem, $lowStock);
                $stock['sources'][] = ["qty" => $quantity, "status" => $this->getStatus($sourceItem), "source_code" => $sourceItem->getSourceCode(), "source_label" => $this->getSourceLabel($sourceItem->getSourceCode()), "source_item_id" => $sourceItem->getSourceItemId()];
                if($sourceItem->getSourceCode() == $this->scopeConfig->getValue("webpos/general/source_stock")){
                    $stock['qty']=$quantity;
                    $stock['status']=$this->getStatus($sourceItem);
                }
            }

            $attributes['webpos_stock'] = $stock;
            $attributes['price_without_tax'] = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount();
            $attributes['price'] = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getValue();
            $taxes = $child->getPriceInfo()->getPrice("final_price")->getAmount()->getAdjustmentAmounts();
            if (is_array($taxes) && !empty($taxes['tax'])) $attributes['tax'] = $taxes['tax'];
            else $attributes['tax'] = 0;
            // Mínimo, máximo e incremento de cantidad en el carrito
            $attributes = array_merge($attributes, $this->getCartQtyLimits($child->getId()));

            // Otros módulos pueden añadir o cambiar datos del hijo (ej: unidad de medida, stock, cantidades)
            $transport = new \Magento\Framework\DataObject($attributes);
            $this->eventManager->dispatch('gsoft_webpos_before_return_simple', [
                'transport' => $transport,
                'product' => $child,
                'parent' => $product,
                'store_id' => $store_id,
            ]);
            $childrenList[] = $transport->getData();

        }

        $exit['result'] = ['attributes' => $attribute_configurable, 'children' => $childrenList];

        $transport = new \Magento\Framework\DataObject($exit);
        $this->eventManager->dispatch('gsoft_webpos_before_return_simples', [
            'transport' => $transport,
            'product' => $product,
            'store_id' => $store_id,
        ]);

        return $transport->getData();

    }
    private function getQty($sku, $sourceItem, $lowStock){
        if($this->scopeConfig->getValue("cataloginventory/item_options/manage_stock")=="0") return 99999;
        if($sourceItem->getStatus()!="1") $quantity=0;
        else {
            $quantity = $sourceItem->getQuantity();
            $quantity -= $lowStock;
            $quantity += $this->getReservedQty($sku, $sourceItem->getSourceCode());
        }
        return $quantity;
    }
    private function getStatus($sourceItem){
        if($this->scopeConfig->getValue("cataloginventory/item_options/manage_stock")=="0") return 1;
        return $sourceItem->getStatus();
    }
    /**
     * Nombre del almacén (fuente de MSI); si no existe, el código
     *
     * @param string $sourceCode
     * @return string
     */
    private function getSourceLabel($sourceCode)
    {
        $sourceCode = (string)$sourceCode;
        if (!array_key_exists($sourceCode, $this->sourceLabels)) {
            try {
                $this->sourceLabels[$sourceCode] = (string)$this->sourceRepository->get($sourceCode)->getName() ?: $sourceCode;
            } catch (\Exception $e) {
                $this->sourceLabels[$sourceCode] = $sourceCode;
            }
        }
        return $this->sourceLabels[$sourceCode];
    }

    private function getReservedQty($sku, $source_code)
    {
        /**@var \Magento\Inventory\Model\Source $source*/
        return $this->getReservationsQuantity->execute($sku, $this->scopeConfig->getValue("webpos/general/stock_item"));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductList($searchCriteria)
    {
        $list = $this->productRepository->getList($searchCriteria);
        $items = $list->getItems();


        $lowStock = $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD_QTY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($items as $k => $p) {
            $extensionAttributes = $p->getExtensionAttributes();
            $extensionAttributes->setData("webpos_price_without_tax", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getBaseAmount());
            $extensionAttributes->setData("webpos_price", $p->getPriceInfo()->getPrice("final_price")->getAmount()->getValue());
            $taxes = $p->getPriceInfo()->getPrice("final_price")->getAmount()->getAdjustmentAmounts();
            if (is_array($taxes) && !empty($taxes['tax'])) $extensionAttributes->setData('webpos_tax', $taxes['tax']);
            else $extensionAttributes->setData('webpos_tax', 0);

            try {
                $stockItem = $this->_stockItemRepository->get($p->getId());
                $backorders = $stockItem && (int) $stockItem->getBackorders() !== \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO;
            } catch (\Exception $e) {
                $backorders = false;
            }
            $extensionAttributes->setData('webpos_backorders', $backorders);

            $sourceItemsBySku = $this->getSourceItemsBySku->execute($p->getSku());


            $stock=$this->stockFactory->create();

            $sources=null;

            foreach ($sourceItemsBySku as $sourceItem) {

                $quantity = $this->getQty($p->getSku(), $sourceItem, $lowStock);

                $source = $this->stockSourceFactory->create();
                $source->setQty($quantity);
                $source->setStatus($this->getStatus($sourceItem));
                $source->setSourceCode($sourceItem->getSourceCode());
                $source->setSourceLabel($this->getSourceLabel($sourceItem->getSourceCode()));
                $source->setSourceId($sourceItem->getSourceItemId());
                $sources[]=$source;
                if($sourceItem->getSourceCode() == $this->scopeConfig->getValue("webpos/general/source_stock")) {
                    $stock->setQty($quantity);
                    $stock->setStatus($this->getStatus($sourceItem));
                }
            }
            $stock->setSources($sources);

            $extensionAttributes->setData("webpos_stock", $stock);

            // Mínimo, máximo e incremento de cantidad en el carrito
            foreach ($this->getCartQtyLimits($p->getId()) as $code => $value) {
                $extensionAttributes->setData($code, $value);
            }

            // Otros módulos pueden añadir datos al producto (sus extension attributes) o cambiar el stock y las cantidades
            $this->eventManager->dispatch('gsoft_webpos_before_return_product', [
                'product' => $p,
                'extension_attributes' => $extensionAttributes,
                'stock' => $stock,
                'search_criteria' => $searchCriteria,
            ]);
            $p->setExtensionAttributes($extensionAttributes);

        }
        $list->setItems($items);
        $this->eventManager->dispatch('gsoft_webpos_before_return_product_list', [
            'result' => $list,
            'search_criteria' => $searchCriteria,
        ]);
        return $list;
    }



}
