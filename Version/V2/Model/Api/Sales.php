<?php

namespace Gsoft\Webpos\Version\V2\Model\Api;

use Gsoft\Webpos\Api\SalesInterface;
use PHPUnit\Runner\Exception;

class Sales implements SalesInterface
{
    protected $storeManager;
    protected $product;

    protected $quoteFactory;
    protected $quoteManagement;
    protected $customerFactory;
    protected $customerRepository;
    protected $orderService;
    protected $quoteRepository;

    protected $productRepository;
    protected $quoteItemFactory;
    protected $productFactory;
    protected $scopeConfig;
    protected $shippingManagement;
    protected $addressRepository;
    protected $shippingAssignmentFactory;
    protected $billingAddressManagement;
    protected $couponModel;
    protected $ruleRepository;
    protected $invoiceService;
    protected $transactionFactory;
    protected $shipmentRepository;
    protected $shipmentFactory;
    protected $taxCalculation;
    protected $paymentMethodRepository;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\PaymentMethodManagementInterface $PaymentMethodManagementInterface,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingManagement,
        \Magento\Checkout\Model\ShippingInformationFactory $shippingAssignmentFactory,
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\SalesRule\Model\Coupon $couponModel,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Tax\Model\Calculation $_taxCalculation


    )
    {
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->productFactory = $productFactory;
        $this->quoteFactory = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;

        $this->orderService = $orderService;
        $this->quoteRepository = $quoteRepository;

        $this->productRepository = $productRepository;
        $this->quoteItemFactory = $quoteItemRepository;
        $this->scopeConfig = $scopeConfig;
        $this->shippingManagement = $shippingManagement;
        $this->addressRepository = $addressRepository;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->couponModel = $couponModel;
        $this->ruleRepository = $ruleRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->taxCalculation=$_taxCalculation;
        $this->paymentMethodRepository=$PaymentMethodManagementInterface;

    }

    protected function getTaxPercent($product){
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $config */
        $countryCode = $this->scopeConfig->getValue("tax/defaults/country");
        $customerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');

        /** @var \Magento\Catalog\Model\Product $product */
        $productTaxClassId = $product->getData('tax_class_id');

        // THE ACTUAL CALCULATION CALL

        /** @var \Magento\Tax\Model\Calculation $taxCalculation */
        $rate = $this->taxCalculation->getRate(
            new \Magento\Framework\DataObject(
                [
                    'country_id' => $countryCode,
                    'customer_class_id' => $customerTaxClassId,
                    'product_class_id' => $productTaxClassId
                ]
            )
        );
        return $rate;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createQuote($data)
    {

        /**@var \Magento\Quote\Model\Quote $quote */
        $id = $this->quoteManagement->createEmptyCart();
        $quote = $this->quoteRepository->getActive($id);
        // $quote->setCurrency();
        $id_store = $data['store_id'];
        if (empty($id_store)) $id_store = 1;
        $quote->setStoreId($id_store);
        $quote->setCurrency();

        $quote->setWebposTerminal($data['terminal']);
        $quote->setWebposAlias($data['webpos_alias']);
        $quote->setWebposUser($data['webpos_user']);
        if(isset($data['discount_percent'])) $quote->setWebposDiscountPercent($data['discount_percent']);
        else $quote->setWebposDiscountPercent(0);
        if(isset($data['discount_fixed'])) $quote->setWebposDiscountFixed($data['discount_fixed']);
        else $quote->setWebposDiscountFixed(0);
        if(isset($data['discount_name'])) $quote->setWebposDiscountLabel($data['discount_name']);
        else $quote->setWebposDiscountLabel(null);

        if (!empty($data['coupon_code'])) $quote->setCouponCode($data['coupon_code']);

        if(empty($data['id_customer']) || !$data['id_customer']>0) {
            $quote->setCustomerEmail("webpos@mail.to");
            $quote->setCustomerFirstname("Compra en tienda");
            $quote->setCustomerLastname("Terminal " . intval($data['terminal']));
            $quote->setCustomerIsGuest(true);
        }else{
            //  $customer = $this->customerFactory->create()->load($data['id_customer']);
            $Icustomer = $this->customerRepository->getById($data['id_customer']);
            /**@var \Magento\Customer\Model\Customer $customer*/
            $quote->assignCustomer($Icustomer);
            /*$quote->setCustomerId($customer->getId());
            $quote->setCustomerEmail($data['customer_data']['email']);
            $quote->setCustomerFirstname($data['customer_data']['firstname']);
            $quote->setCustomerLastname($data['customer_data']['lastname']);*/
            if(!empty($data['customer_billing_address']['id'])){
                $address=$this->addressRepository->getById($data['customer_billing_address']['id']);
                if(!empty($address) && $address->getCustomerId()==$Icustomer->getId()){
                    $quote->getBillingAddress()->importCustomerAddressData($address);
                    $quote->getBillingAddress()->setMiddlename(".");
                }
            }
            if(!empty($data['customer_shipping_address']['id'])){
                $address=$this->addressRepository->getById($data['customer_shipping_address']['id']);

                if(!empty($address) && $address->getCustomerId()==$Icustomer->getId()){
                    $quote->getShippingAddress()->importCustomerAddressData($address);
                    $quote->getShippingAddress()->setMiddlename(".");
                }
            }


        }
        foreach ($data['items'] as $k => $item) {
            try {
                // $quoteItems = $quote->getItems();

                $_product = $this->productRepository->getById($item['id']);
                /**@var \Magento\Catalog\Pricing\Price\FinalPrice $price */

                $params = [];
                $params['product'] = $_product->getId();
                $params['qty'] = $item['qty'];

                //$_product->setPrice($_product->getPriceInfo()->getPrice("final_price")->getAmount()->getValue());
                // $_product->setBasePrice($_product->getPriceInfo()->getPrice("final_price")->getAmount()->getValue());
                $params['custom_option'] = ["webpos_item_id" => $item['item_id']];

                // $params['discount_amount'] = 10;
                $_product->addCustomOption('webpos_item_id', $item['item_id']);
                $_product->setData("webpos_item_id", $item['item_id']);
                if (!empty($item['custom_price'])){
                    if(!$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        $rate = $this->getTaxPercent($_product);
                        if ($rate > 0) {
                            $params['custom_price'] = $item['custom_price'] / (1 + $rate * 0.01);
                        } else $params['custom_price'] = $item['custom_price'];
                    }else $params['custom_price'] = $item['custom_price'];
                }
                if (!empty($item['selected_values'])) {

                    foreach ($item['selected_values'] as $att) {
                        $options[$att['id']] = $att['value'];
                    }
                    $params['super_attribute'] = $options;

                }
                $obj = new\Magento\Framework\DataObject();
                $obj->setData($params);
                $quote->addProduct($_product, $obj);


            } catch (\Exception $e) {

                /* echo $e->getMessage();
                 print_r($e->getTraceAsString());*/
                $data['items'][$k]['quote_item_id'] = 0;
                $data['items'][$k]['stock'] = 0;
            }
        }
        $free_shipping_qty=$this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $shipping_method=$this->scopeConfig->getValue("webpos/general/shipping_default", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates();
        foreach($shippingAddress->getAllShippingRates() as $rate){
            if($rate->getCode()=="freeshipping_freeshipping"){
                $shipping_method="freeshipping_freeshipping";
                break;
            }
        }


        $shippingAddress->setShippingMethod($shipping_method);

        $quote->collectTotals();

        if( $shipping_method!=="freeshipping_freeshipping" && $free_shipping_qty < $quote->getSubtotalWithDiscount()){
            $quote->setTotalsCollectedFlag(false);
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates();
            $shipping_method="webpos_webpos";
            $shippingAddress->setShippingMethod($shipping_method);

            $quote->collectTotals();
        }

        $quote->save();

        foreach ($quote->getAllItems() as $inserted) {
            foreach ($data['items'] as $k => $item) {
                if ($inserted->getProduct()->getData("webpos_item_id") == $item['item_id']) {

                    $data['items'][$k]['quote_item_id'] = $inserted->getId();
                    if ($inserted->getCustomPrice() > 0) {
                        //comprobamos si el iva esta incluido en los precios
                        if(!$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                            $data['items'][$k]['custom_price_without_tax'] = $inserted->getCustomPrice();
                            $data['items'][$k]['custom_price'] = round($inserted->getCustomPrice() * (1 + $inserted->getTaxPercent() * 0.01), 2);
                        }else{
                            $data['items'][$k]['custom_price_without_tax'] = $inserted->getCustomPrice();
                            $data['items'][$k]['custom_price'] = round($inserted->getCustomPrice() * (1 + $inserted->getTaxPercent() * 0.01), 2);
                        }
                    } else {
                        $data['items'][$k]['custom_price'] = 0;
                        $data['items'][$k]['price'] = $inserted->getPriceInclTax();
                        $data['items'][$k]['price_without_tax'] = $inserted->getPrice();
                    }
                    $data['items'][$k]['row_total'] = $inserted->getRowTotalInclTax();
                    $data['items'][$k]['row_total_without_tax'] = $inserted->getRowTotal();
                    $data['items'][$k]['tax'] = $inserted->getTaxAmount();
                    $data['items'][$k]['tax_percent'] = $inserted->getTaxPercent();
                    $data['items'][$k]['qty'] = $inserted->getQty();
                    $data['items'][$k]['discount'] = $inserted->getDiscountAmount();
                }
            }
        }
        $data['id'] = $quote->getId();
        $data['discount_amount'] = abs($quote->getShippingAddress()->getDiscountAmount());
        $data['discount_cart'] = $quote->getShippingAddress()->getDiscountDescription();
        $data['coupon_code'] = $quote->getCouponCode();
        $data['subtotal_with_discount'] = $quote->getSubtotalWithDiscount();
        $data['shipping_amount'] = $quote->getShippingAddress()->getShippingAmount();
        $data['shipping_method'] = $shipping_method;
        $totals = $quote->getTotals();

        /*@var \Magento\Quote\Model\Quote\Address\Total $tax*/
        $tax = !empty($totals['tax']) ? $totals['tax'] : null;
        if (!empty($tax)) $data['tax'] = $tax->getData('value');
        $data['subtotal'] = $quote->getSubtotal();
        $data['grand_total'] = $quote->getGrandTotal();
        $data['payment_methods']=[];
        $payments=$this->scopeConfig->getValue("webpos/general/adminpayments", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $quote->getStoreId());
        if(empty($payments)) $payments="webposcash";
        $payments=explode(",", $payments);

        foreach($this->paymentMethodRepository->getList($quote->getId()) as $payment){

            if(!in_array($payment->getCode(), $payments)) continue;
            $data['payment_methods'][]=['code'=>$payment->getCode(),'label'=>$payment->getTitle()];
        }
        return [$data];
    }

    public function getInfoCoupon($couponCode)
    {

        try {
            $ruleId = $this->couponModel->loadByCode($couponCode)->getRuleId();
            $rule = $this->ruleRepository->getById($ruleId);
            return $rule->getDiscountAmount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function spendCoupon($couponCode)
    {

        try {
            $coupon = $this->couponModel->loadByCode($couponCode);
            $coupon->setTimesUsed($coupon->getTimesUsed() + 1);
            $coupon->save();

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @param mixed $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareQuote($data)
    {
        /**@var \Magento\Quote\Model\Quote $quote */

        $quote = $this->quoteRepository->getActive($data['id']);
        $spend_coupon = null;
        $total_coupons = 0;
        try {
            if (!empty($data['coupon_code'])) {
                $total_coupons = $this->getInfoCoupon($data['coupon_code']);
                if ($total_coupons > 0) {

                    if (!empty($data['payments'])) {
                        foreach ($data['payments'] as $k => $payment) {
                            if ($payment['code'] == "webposcoupon") {
                                unset($data['payments'][$k]);
                                break;
                            }
                        }
                    }
                    $data['payments'][] = ['code' => "webposcoupon", 'label' => 'app.quote.payment_coupon', 'name' => 'Vale descuento', 'delivered' => $total_coupons, 'reference' => $data['coupon_code'], 'coupon' => $data['coupon_code']];
                    $spend_coupon = $data['coupon_code'];
                }
            }
            if($quote->getCustomerId()==0) {
                $customer = $this->customerRepository->getById($this->scopeConfig->getValue('webpos/general/guest_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $quote->assignCustomer($customer);
                $quote->setCustomerIsGuest(0);

                $billingAddressId = $customer->getDefaultBilling();
                $billingAddress = $this->addressRepository->getById($billingAddressId);
                $qbillingAddress = $this->assignAddress($quote->getBillingAddress(), $billingAddress);
                $quote->setBillingAddress($qbillingAddress);

                $shippingAddressId = $customer->getDefaultShipping();
                $shippingAddress = $this->addressRepository->getById($shippingAddressId);
                $qshippingAddress = $this->assignAddress($quote->getShippingAddress(), $shippingAddress);
                $quote->setShippingAddress($qshippingAddress);

            }
            $payment = $quote->getPayment();
            $payment_code='webposcash';
            if (!empty($data['payments'])) {
                // $payment_code=$data['payments'][0]['code'];
                foreach($data['payments'] as $payment_item){
                    $payment_code = $payment_item['code'];
                    break;
                }
            }
            $payment->setMethod($payment_code);

            $payment->setAdditionalInformation("webpos", json_encode($data['payments']));
            $quote->setPayment($payment);

            $shipping_method=$this->scopeConfig->getValue("webpos/general/shipping_default", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates();
            foreach($shippingAddress->getAllShippingRates() as $rate){
                if($rate->getCode()=="freeshipping_freeshipping"){
                    $shipping_method="freeshipping_freeshipping";
                    break;
                }
            }

            $shippingAddress->setShippingMethod($shipping_method);

            $free_shipping_qty=$this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $quote/*->setCouponCode('')*/->collectTotals();
            if( $shipping_method!=="freeshipping_freeshipping" && $free_shipping_qty < $quote->getSubtotalWithDiscount()){
                $quote->setTotalsCollectedFlag(false);
                $shippingAddress->setCollectShippingRates(true)
                    ->collectShippingRates();
                $shipping_method="webpos_webpos";
                $shippingAddress->setShippingMethod($shipping_method);

                $quote->collectTotals();
            }

            $quote->save();

            $order = $this->quoteManagement->submit($quote);
            $data['increment_id'] = $order->getIncrementId();
            $data['id'] = $order->getId();
            if (!empty($spend_coupon)) $this->spendCoupon($spend_coupon);
        } catch (\Exception $e) {
            throw $e;
        }
        $errores=[];

        try {
            if($this->scopeConfig->getValue("webpos/general/create_shipment", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                // load order from database
                if ($order->canShip()) {
                    $items = [];

                    foreach ($order->getAllItems() as $item) {
                        $items[$item->getItemId()] = $item->getQtyOrdered();
                    }


                    // create the shipment
                    $shipment = $this->shipmentFactory->create($order, $items);
                    $shipment->register();
                    // save the newly created shipment
                    $transactionSave = $this->transactionFactory->create()->addObject($shipment);
                    $transactionSave->save();
                    $data['shipment_id'] = $shipment->getId();
                }
            }

        } catch (\Exception $e) {
            $errores[]=$e->getMessage();
        }
        try {
            if($this->scopeConfig->getValue("webpos/general/create_invoice", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                //generate invoice
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                // $order->setState("processing");

                $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $data['invoice_id'] = $invoice->getId();
            }

        } catch (\Exception $e) {
            $errores[]=$e->getMessage();
        }

        $order->setStatus($this->scopeConfig->getValue("webpos/general/order_status", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $order->addStatusHistoryComment(__($this->scopeConfig->getValue("webpos/general/payment_description", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)), false);
        $order->save();
        $data['errors']=$errores;

        return [$data];


    }

    private function assignAddress($address, \Magento\Customer\Model\Data\Address $data)
    {
        $address->setCustomerAddressId($data->getId());
        $address->setCustomerId($data->getCustomerId());
        $address->setFirstname($data->getFirstname());
        $address->setLastname($data->getLastname());
        $address->setStreet($data->getStreet());
        $address->setCity($data->getCity());
        $address->setTelephone($data->getTelephone());
        $address->setPostcode($data->getPostcode());
        $address->setCountryId($data->getCountryId());
        return $address;
    }


}
