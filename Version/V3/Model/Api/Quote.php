<?php

namespace Gsoft\Webpos\Version\V3\Model\Api;

use Gsoft\Webpos\Api\QuoteInterface;

class Quote implements QuoteInterface
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
    protected $eventManager;
    protected $logger;
    protected $orderPaymentFactory;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface                   $storeManager,
        \Magento\Catalog\Model\Product                               $product,
        \Magento\Catalog\Api\ProductRepositoryInterface              $productRepository,
        \Magento\Catalog\Model\ProductFactory                        $productFactory,
        \Magento\Quote\Model\QuoteFactory                            $quote,
        \Magento\Quote\Model\QuoteManagement                         $quoteManagement,
        \Magento\Customer\Model\CustomerFactory                      $customerFactory,
        \Magento\Quote\Api\CartRepositoryInterface                   $quoteRepository,
        \Magento\Quote\Api\PaymentMethodManagementInterface          $PaymentMethodManagementInterface,
        \Magento\Quote\Model\Quote\ItemFactory                       $quoteItemRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface            $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface             $addressRepository,
        \Magento\Sales\Model\Service\OrderService                    $orderService,
        \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingManagement,
        \Magento\Checkout\Model\ShippingInformationFactory           $shippingAssignmentFactory,
        \Magento\Quote\Api\BillingAddressManagementInterface         $billingAddressManagement,
        \Magento\SalesRule\Model\Coupon                              $couponModel,
        \Magento\SalesRule\Api\RuleRepositoryInterface               $ruleRepository,
        \Magento\Sales\Model\Service\InvoiceService                  $invoiceService,
        \Magento\Framework\DB\TransactionFactory                     $transactionFactory,
        \Magento\Sales\Model\Order\ShipmentRepository                $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory                   $shipmentFactory,
        \Magento\Tax\Model\Calculation                               $_taxCalculation,
        \Magento\Framework\Event\ManagerInterface                    $eventManager,
        \Psr\Log\LoggerInterface                                     $logger,
        \Gsoft\Webpos\Model\OrderPaymentFactory                      $orderPaymentFactory


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
        $this->taxCalculation = $_taxCalculation;
        $this->paymentMethodRepository = $PaymentMethodManagementInterface;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->orderPaymentFactory = $orderPaymentFactory;
    }

    protected function getTaxPercent($product, $countryCode = null, $customerTaxClassId = null)
    {
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $config */
        if (empty($countryCode)) $countryCode = $this->scopeConfig->getValue("tax/defaults/country");
        if (empty($customerTaxClassId)) $customerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');

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


        $quote = $id = null;
        /**@var \Magento\Quote\Model\Quote $quote */
        if (!empty($data['id'])) {
            $id = $data['id'];
            $quote = $this->quoteFactory->create()->load($id);
            if ($quote->getIsActive()) {
                $quote->delete();
            }
            $quote = $id = null;

        }

        if (empty($id)) {
            $id = $this->quoteManagement->createEmptyCart();
            $quote = $this->quoteRepository->getActive($id);
        }
        // $quote->setCurrency();
        $id_store = $data['store_id'];
        if (empty($id_store)) $id_store = 1;
        $quote->setStoreId($id_store);
        $quote->setCurrency();

        $quote->setWebposTerminal($data['terminal']);
        $quote->setWebposAlias($data['webpos_alias']);
        $quote->setWebposUser($data['webpos_user']);
        if (isset($data['discount_percent'])) $quote->setWebposDiscountPercent($data['discount_percent']);
        else $quote->setWebposDiscountPercent(0);
        if (isset($data['discount_fixed'])) $quote->setWebposDiscountFixed($data['discount_fixed']);
        else $quote->setWebposDiscountFixed(0);
        if (isset($data['discount_name'])) $quote->setWebposDiscountLabel($data['discount_name']);
        else $quote->setWebposDiscountLabel(null);
        if (empty($data['id_customer']) || !$data['id_customer'] > 0) {
            $id_guest = $this->scopeConfig->getValue("webpos/general/guest_customer");
            if (!empty($id_guest)) $data['id_customer'] = $id_guest;
        }
        $tax_class_id = null;
        if (empty($data['id_customer']) || !$data['id_customer'] > 0) {
            $quote->setCustomerEmail("webpos@mail.to");
            $quote->setCustomerFirstname("Compra en tienda");
            $quote->setCustomerLastname("Terminal " . intval($data['terminal']));
            $quote->setCustomerIsGuest(true);
        } else {
            $customerModel = $this->customerFactory->create()->load($data['id_customer']);
            $tax_class_id = $customerModel->getTaxClassId();
            $Icustomer = $this->customerRepository->getById($data['id_customer']);
            /**@var \Magento\Customer\Model\Customer $customer */
            $quote->assignCustomer($Icustomer);
            /*$quote->setCustomerId($customer->getId());
            $quote->setCustomerEmail($data['customer_data']['email']);
            $quote->setCustomerFirstname($data['customer_data']['firstname']);
            $quote->setCustomerLastname($data['customer_data']['lastname']);*/
            if (!empty($data['customer_billing_address']['id'])) {
                $address = $this->addressRepository->getById($data['customer_billing_address']['id']);
                if (!empty($address) && $address->getCustomerId() == $Icustomer->getId()) {
                    $quote->getBillingAddress()->importCustomerAddressData($address);
                    $quote->getBillingAddress()->setMiddlename(".");
                }
            }
            if (!empty($data['customer_shipping_address']['id'])) {
                $address = $this->addressRepository->getById($data['customer_shipping_address']['id']);

                if (!empty($address) && $address->getCustomerId() == $Icustomer->getId()) {
                    $quote->getShippingAddress()->importCustomerAddressData($address);
                    $quote->getShippingAddress()->setMiddlename(".");
                }
            }
            $quote->setCustomerIsGuest(false);
            $customer_name = $Icustomer->getFirstname() . " " . $Icustomer->getLastname();

            $this->eventManager->dispatch('gsoft_webpos_before_returncustomer_name', ['customer_name' => &$customer_name, "quote" => $quote, "customer" => $Icustomer]);

            $data['customer_name'] = $customer_name;

        }
        if (!empty($data['coupon_code'])) $quote->setCouponCode($data['coupon_code']);
        foreach ($data['items'] as $k => $item) {
            if (!empty($item['gift'])) continue;
            try {

                if (!empty($item['customized']) && $item['custom_price'] == 0) $item['custom_price'] = 0.0001;
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

                //iva
                $rate = $this->getTaxPercent($_product, $quote->getShippingAddress()->getCountryId(), $tax_class_id);

                $item['original_custom_price'] = $item['custom_price']??null;

                if (!empty($item['customized'])) {
                    if (!$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        if ($rate > 0) {
                            $params['custom_price'] = $item['custom_price'] / (1 + $rate * 0.01);
                        } else $params['custom_price'] = $item['custom_price'];
                    } else $params['custom_price'] = $item['custom_price'];
                }

                if (!empty($item['child_id'])) {
                    $child = $this->productFactory->create()->load($item['child_id']);
                    $parent = $this->productFactory->create()->load($item['id']);
                    $_product = $parent;
                    $productAttributeOptions = $parent->getTypeInstance(true)->getConfigurableAttributesAsArray($parent);
                    foreach ($productAttributeOptions as $option) {
                        $options[$option['attribute_id']] = $child->getData($option['attribute_code']);
                    }
                    $params['super_attribute'] = $options;


                    /*  foreach ($item['selected_values'] as $att) {
                          $options[$att['id']] = $att['value'];
                      }
                      $params['super_attribute'] = $options;*/

                }
                $data['items'][$k]['quote_item_id'] = 0;

                $obj = new \Magento\Framework\DataObject();
                $obj->setData($params);
                $quote->addProduct($_product, $obj);


            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
                // echo $e->getMessage();
                /* echo $e->getMessage();
                 print_r($e->getTraceAsString());*/
                //$data['items'][$k]['stock'] = 0;
            }
        }


        $shipping_method = $this->scopeConfig->getValue("webpos/general/shipping_default");
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates();
        foreach ($shippingAddress->getAllShippingRates() as $rate) {
            if ($rate->getCode() == "freeshipping_freeshipping") {
                $shipping_method = "freeshipping_freeshipping";
                break;
            }
        }
        $shippingAddress->setShippingMethod($shipping_method);

        $quote->collectTotals()->save();

        $cloned_items = [];
        foreach ($data['items'] as $k => $item) {
            if (empty($item['gift'])) {
                $cloned_items[] = $item;
            }
        }
        $data['items'] = $cloned_items;
        /**@var \Magento\Quote\Model\Quote\Item $inserted */
        foreach ($quote->getAllItems() as $inserted) {
            $uniqid = $inserted->getBuyRequest()->getCustomOption("webpos_item_id");

            if ($inserted->getParentItemId()) continue;

            $found = false;
            foreach ($data['items'] as $k => $item) {

                if ($uniqid == $item['item_id']) {
                    $found = true;
                    $data['items'][$k]['quote_item_id'] = $inserted->getId();
                    if ($inserted->getCustomPrice() > 0) {
                        //comprobamos si el iva esta incluido en los precios
                        if (!$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                            $data['items'][$k]['custom_price_without_tax'] = $inserted->getCustomPrice();
                            $data['items'][$k]['custom_price'] = round($inserted->getCustomPrice() * (1 + $inserted->getTaxPercent() * 0.01), 4);
                        } else {

                            $data['items'][$k]['custom_price_without_tax'] = $inserted->getCustomPrice();
                            $data['items'][$k]['custom_price'] = $inserted->getPriceInclTax() /*$inserted->getCustomPrice() * (1 + $inserted->getTaxPercent() * 0.01)*/
                            ;
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
                    //hay que ver si tenemos que añadir o quitar el iva al descuento
                    $data['items'][$k]['discount'] = $inserted->getDiscountAmount();
                }
            }
            if (!$found) {
                //se ha añadido un producto nuevo, por ej, un regalo
                $new_item = [];

                $new_item['quote_item_id'] = $inserted->getId();
                $new_item['sku'] = $inserted->getSku();
                $new_item['name'] = $inserted->getName();
                $new_item['gift'] = 1;
                $new_item['item_id'] = uniqid();
                $new_item['custom_price'] = 0;
                $new_item['price'] = $inserted->getPriceInclTax();
                $new_item['price_without_tax'] = $inserted->getPrice();
                $new_item['row_total'] = $inserted->getRowTotalInclTax();
                $new_item['row_total_without_tax'] = $inserted->getRowTotal();
                $new_item['tax'] = $inserted->getTaxAmount();
                $new_item['tax_percent'] = $inserted->getTaxPercent();
                $new_item['qty'] = $inserted->getQty();
                $new_item['type'] = $inserted->getProductType();
                $new_item['id'] = $inserted->getProduct()->getId();
                $new_item['stock'] = 9999;
                $new_item['extra_stocks'] = [];
                //hay que ver si tenemos que añadir o quitar el iva al descuento
                $new_item['discount'] = $inserted->getDiscountAmount();
                $data['items'][] = $new_item;
            }
        }
        $data['id'] = $quote->getId();
        $data['discount_amount'] = abs($quote->getShippingAddress()->getDiscountAmount());
        $data['discount_cart'] = $quote->getShippingAddress()->getDiscountDescription();
        $data['coupon_code'] = $quote->getCouponCode();
        $data['subtotal_with_discount'] = max(0, $quote->getSubtotalWithDiscount());
        $data['shipping_amount'] = max(0, $quote->getShippingAddress()->getShippingAmount());
        $totals = $quote->getTotals();

        /*@var \Magento\Quote\Model\Quote\Address\Total $tax*/
        $tax = !empty($totals['tax']) ? $totals['tax'] : null;
        if (!empty($tax)) $data['tax'] = $tax->getData('value');
        $data['subtotal'] = max(0, $quote->getSubtotal());
        $data['grand_total'] = max(0, $quote->getGrandTotal());
        $data['payment_methods'] = [];
        $payments = $this->scopeConfig->getValue("webpos/general/adminpayments", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $quote->getStoreId());
        if (empty($payments)) $payments = "webposcash";
        $payments = explode(",", $payments);

        foreach ($this->paymentMethodRepository->getList($quote->getId()) as $payment) {

            if (!in_array($payment->getCode(), $payments)) continue;
            $data['payment_methods'][] = ['code' => $payment->getCode(), 'label' => $payment->getTitle()];
        }
        $this->eventManager->dispatch('gsoft_webpos_before_return_quote', ['quote' => &$data]);
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
        //parece que se actualiza. ¿Verificar en 2.3?
        return;
        try {
            $coupon = $this->couponModel->loadByCode($couponCode);
            $coupon->setTimesUsed($coupon->getTimesUsed() + 1);
            $coupon->save();

        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function updateCouponAmount($couponCode, $remainDiscount)
    {

        try {
            $ruleId = $this->couponModel->loadByCode($couponCode)->getRuleId();
            $rule = $this->ruleRepository->getById($ruleId);
            if ($rule->getDescription() != "giftcard") return;
            $rule->setDiscountAmount($remainDiscount);
            $rule->setUsesPerCoupon($rule->getUsesPerCoupon() + 1);
            $rule->setUsesPerCustomer($rule->getUsesPerCustomer() + 1);
            $this->ruleRepository->save($rule);
            return $rule->$remainDiscount;
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
                    $coupon_total = $total_coupons;

                    $data['payments'][] = ['code' => "webposcoupon", 'label' => 'app.quote.payment_coupon', 'name' => 'Vale descuento', 'delivered' => $total_coupons, 'reference' => $data['coupon_code'], 'coupon' => $data['coupon_code']];
                    $spend_coupon = $data['coupon_code'];
                }
            }
            $quote->setCustomerIsGuest(0);
            if ($quote->getCustomerId() == 0) {
                $customer = $this->customerRepository->getById($this->scopeConfig->getValue('webpos/general/guest_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $quote->assignCustomer($customer);


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
            $payment_code = 'webposcash';
            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $payment_item) {
                    $payment_code = $payment_item['code'];
                    break;
                }
            }
            $payment->setMethod($payment_code);

            $payment->setAdditionalInformation("webpos", json_encode($data['payments']));
            $quote->setPayment($payment);


            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($shipping_method = $this->scopeConfig->getValue("webpos/general/shipping_default"));


            $quote/*->setCouponCode('')*/ ->collectTotals();

            $remain_coupon_amount = 0;
            if ($total_coupons > 0) {
                $discountAmount = 0;
                foreach ($quote->getAllVisibleItems() as $item) {
                    $discountAmount += ($item->getDiscountAmount() ? $item->getDiscountAmount() : 0);
                }

                if ($total_coupons > $discountAmount) {
                    if (!empty($data['payments'])) {
                        foreach ($data['payments'] as $k => $paymentItem) {
                            if ($paymentItem['code'] == "webposcoupon") {
                                $paymentItem['delivered'] = $discountAmount;
                                $data['payments'][$k] = $paymentItem;

                                $payment->setAdditionalInformation("webpos", json_encode($data['payments']));
                                $quote->setPayment($payment);
                                break;
                            }
                        }
                    }
                    $remain_coupon_amount = $total_coupons - $discountAmount;


                }
            }
            $quote->save();


            $order = $this->quoteManagement->submit($quote);
            if(empty($order)) throw new \Exception("Hubo un error al crear el pedido");
            if ($remain_coupon_amount > 0) {
                $this->updateCouponAmount($data['coupon_code'], $remain_coupon_amount);

            }

            $data['increment_id'] = $order->getIncrementId();
            $data['id'] = $order->getId();
            if (!empty($spend_coupon)) $this->spendCoupon($spend_coupon);

            foreach ($data["payments"] as $orderpayment) {
                $webpospayment = $this->orderPaymentFactory->create();
                $webpospayment->setData($orderpayment);
                $webpospayment->setData("order_id", $order->getId());
                $webpospayment->setData("created_at", $order->getCreatedAt());
                $webpospayment->save();
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            throw $e;
        }
        $errores = [];

        try {
            if ($this->scopeConfig->getValue("webpos/general/create_shipment")) {
                // load order from database
                if ($order->canShip()) {
                    $items = [];

                    foreach ($order->getAllItems() as $item) {
                        $items[$item->getItemId()] = $item->getQtyOrdered();
                    }


                    // create the shipment
                    $shipment = $this->shipmentFactory->create($order, $items);
                    $shipment->getExtensionAttributes()->setSourceCode($this->scopeConfig->getValue("webpos/general/source_stock"));
                    $shipment->register();
                    // save the newly created shipment
                    $transactionSave = $this->transactionFactory->create()->addObject($shipment);
                    $transactionSave->save();
                    $data['shipment_id'] = $shipment->getId();
                }
            }

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            $errores[] = $e->getMessage();
        }
        try {
            if ($this->scopeConfig->getValue("webpos/general/create_invoice")) {
                //generate invoice
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                /*   $order->setState("complete");
                   $order->setStatus("complete");*/
                $order->addStatusHistoryComment(__($this->scopeConfig->getValue("webpos/general/payment_description")), false);
                $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $data['invoice_id'] = $invoice->getId();
            }


        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            $errores[] = $e->getMessage();
        }
        $order->setStatus($this->scopeConfig->getValue("webpos/general/order_status"));
        $order->addStatusHistoryComment(__($this->scopeConfig->getValue("webpos/general/payment_description")), false);

        try {
            if ($this->scopeConfig->getValue("webpos/general/enabled_ga", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->ga4SendData($order);
            }
        } catch (\Exception $e) {
            $errores[] = $e->getMessage();
        }

        $data['errors'] = $errores;

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

    public function ga4SendData($order)
    {

        $test = false;
        $gid = $this->scopeConfig->getValue("webpos/general/ga_gid", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $secret = $this->scopeConfig->getValue("webpos/general/ga_secret", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . $gid . '&api_secret=' . $secret;

        /**@var \Magento\Sales\Model\Order $order */
        $items = [];
        if (!empty($order->getAllVisibleitems())) {
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = [
                    'item_id' => $item->getSku(),
                    'quantity' => intval($item->getQtyOrdered()),
                    'price' => $item->getPrice(),
                    'item_name' => "patata",//$item->getname(),
                    // Otros atributos del producto, si los hay
                ];
            }
        }
        $ref = $order->getIncrementId();
        if ($test) $ref .= "----" . uniqid();
        $data = [
            'client_id' => $this->getCID(),
            'events' => [
                [
                    'name' => 'purchase',
                    'params' => [
                        'engagement_time_msec' => "100",
                        'session_id' => uniqid(),
                        'transaction_id' => $ref,
                        'value' => $order->getGrandTotal(),
                        'currency' => $order->getOrderCurrencyCode(),
                        'tax' => $order->getTaxAmount(),
                        'shipping' => $order->getShippingAmount(),
                        'items' => $items
                    ],
                ],
            ]
        ];


        $data = json_encode($data);

        $ch = curl_init();
        if ($test) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        //curl_setopt($ch, CURLOPT_USERAGENT,		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = null;

        try {
            $response = curl_exec($ch);
            curl_close($ch);
            if ($test) {
                echo $data;
                echo $response;
                print_r(curl_getinfo($ch));
            }


        } catch (\Exception $e) {
            curl_close($ch);
        }


        return $response;
    }

    private function getCID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

}
