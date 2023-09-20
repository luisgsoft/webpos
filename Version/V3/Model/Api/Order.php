<?php

namespace Gsoft\Webpos\Version\V3\Model\Api;

use Gsoft\Webpos\Api\OrderInterface;

class Order implements OrderInterface
{
    protected $storeManager;
    protected $orderRepository;
    protected $orderPaymentFactory;
    protected $timezoneInterface;
    protected $shipmentFactory;
    protected $scopeConfig;
    protected $invoiceService;
    protected $transactionFactory;
    protected $logger;
    protected $db;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface           $storeManager,
        \Magento\Sales\Model\OrderRepository                 $orderRepository,
        \Gsoft\Webpos\Model\OrderPaymentFactory              $orderPaymentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Sales\Model\Order\ShipmentFactory           $shipmentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig,
        \Magento\Sales\Model\Service\InvoiceService          $invoiceService,
        \Magento\Framework\DB\TransactionFactory             $transactionFactory,
        \Gsoft\Webpos\Logger\Logger                          $logger,
        \Magento\Framework\App\ResourceConnection            $resource

    )
    {
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->shipmentFactory = $shipmentFactory;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->db = $resource->getConnection();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function pay($payment)
    {
        $errores = [];
        try {
            $order_id = $payment->getOrderId();
            $order = $this->orderRepository->get($order_id);
            $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
            $due = $order->getTotalDue() - $order->getData("webpos_installments");
            if ($payment->getAmount() > $due) $payment->setAmount($due);
            $order_payment = $order->getPayment();
            $info = $order_payment->getAdditionalInformation();
            $payment_array = ['code' => $payment->getCode(), 'label' => $payment->getLabel(), 'delivered' => $payment->getDelivered(), 'reference' => $payment->getReference(), 'name' => $payment->getName(), 'amount' => $payment->getAmount()];
            $info['webpos'][] = $payment_array;
            $info['instructions'] .= $payment->getName() . " x " . number_format($payment->getAmount(), 2) . " (" . $dateTime . ")" . "\n";
            $order_payment->setAdditionalInformation($info);
            $order->setPayment($order_payment);
            $installments = doubleval($order->getData("webpos_installments"));
            $installments += $payment->getAmount();
            $order->setData("webpos_installments", $installments);
            $due = $order->getTotalDue() - $order->getData("webpos_installments");
            if ($due < 0.01) $order->setData("webpos_installments", null);
            $this->orderRepository->save($order);

            $webpospayment = $this->orderPaymentFactory->create();
            $webpospayment->setData($payment_array);
            $webpospayment->setData("order_id", $order->getId());
            $webpospayment->setData("terminal", $order->getData("webpos_terminal"));
            $webpospayment->setData("user", $order->getData("webpos_user"));
            $webpospayment->setData("increment_id", $order->getIncrementId());
            $webpospayment->setData("created_at", $dateTime);
            $webpospayment->save();

            if ($due < 0.01) {
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
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            $errores[] = $e->getMessage();
        }

        $data['errors'] = $errores;

        return [$data];
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function book($order_id, $status)
    {
        $errores = [];
        $data = [];
        try {
            $value = ($status != 1) ? "null" : "1";
            $this->db->beginTransaction();
            $sql = [];
            $sql[] = "update sales_order set webpos_booking=" . $value . " where entity_id=" . $order_id;
            $sql[] = "update sales_order_grid set webpos_booking=" . $value . " where entity_id=" . $order_id;
            $sql[] = "update sales_invoice set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update sales_invoice_grid set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update sales_shipment set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update sales_shipment_grid set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update sales_creditmemo set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update sales_creditmemo_grid set webpos_booking=" . $value . " where order_id=" . $order_id;
            $sql[] = "update webpos_order_payment set webpos_booking=" . $value . " where order_id=" . $order_id;
            foreach ($sql as $s) {
                $this->db->query($s);
            }
            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            $errores[] = $e->getMessage();
        }
        $data['errors'] = $errores;

        return [$data];
    }
}
