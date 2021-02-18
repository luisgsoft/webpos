<?php

namespace Gsoft\Webpos\Model\Api;

use Gsoft\Webpos\Api\ConfigInterface;

class Config implements ConfigInterface
{
    protected $storeManager;

    protected $scopeConfig;
    protected $configFactory;
    /**
     * Payment Helper Data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;
    protected $paymentFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Gsoft\Webpos\Model\Api\Data\ConfigFactory $configFactory,
        \Gsoft\Webpos\Model\Api\Data\PaymentFactory $paymentFactory

    )
    {
        $this->storeManager = $storeManager;

        $this->scopeConfig = $scopeConfig;
        $this->_paymentHelper = $paymentHelper;
        $this->configFactory=$configFactory;
        $this->paymentFactory=$paymentFactory;
    }



    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getConfig($website_id)
    {
        $exit = $this->configFactory->create();



        $exit->setPayments($this->getPayments($website_id));
        $exit->setWebsites($this->getWebsites());
        $exit->setStores($this->getStores());
        $exit->setSourceStock($this->scopeConfig->getValue("webpos/general/source_stock", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $website_id));
        return $exit;
    }
    function getPayments($website_id){
        $return_payments=[];

        $payments=$this->scopeConfig->getValue("webpos/general/adminpayments", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $website_id);
        if(empty($payments)) $payments="webposcash";
        $payments=explode(",", $payments);
        foreach ($this->_paymentHelper->getPaymentMethodList() as $k => $payment) {

            if(!in_array($k, $payments)) continue;
            /*if($k=="webposcash"){

                $p = $this->paymentFactory->create();
                $p->setCode("webposcash");
                $p->setLabel("app.quote.cash");
                $return_payments[] = $p;

                $p = $this->paymentFactory->create();
                $p->setCode("webposcard");
                $p->setLabel("app.quote.card");
                $return_payments[] = $p;

            }else {*/
            $p = $this->paymentFactory->create();
            $p->setCode($k);
            $p->setLabel($payment);
            $return_payments[] = $p;
            //}

        }
        return $return_payments;
    }

    function getStores(){
        $stores = $this->storeManager->getStores();
        $avail=[];
        foreach($stores as $store){
            if($this->scopeConfig->getValue("webpos/general/enabled", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId())) $avail[]=$store;
        }
        return $avail;
    }
    function getWebsites(){
        $websites = $this->storeManager->getWebsites();
        $avail=[];
        foreach($websites as $website){
            if($this->scopeConfig->getValue("webpos/general/enabled", \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $website->getId())) $avail[]=$website;
        }
        return $avail;
        return $websites;
    }
}