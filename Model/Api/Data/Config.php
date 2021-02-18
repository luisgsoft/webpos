<?php

namespace Gsoft\Webpos\Model\Api\Data;

class Config implements \Gsoft\Webpos\Api\Data\ConfigInterface{

    protected $payments;
    protected $websites;
    protected $stores;
    protected $sourcestock;

    public function getPayments(){
            return $this->payments;
    }

    /**
     * Sets the payments.
     *
     * @param mixed $payments
     * @return $this
     */
    public function setPayments($payments){
        $this->payments=$payments;
    }
    /**
     * Returns the websites
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[] $websites
     */
    public function getWebsites(){
        return $this->websites;
    }

    /**
     * Sets the payments.
     *
     * @param \Magento\Store\Api\Data\WebsiteInterface[] $websites
     * @return $this
     */
    public function setWebsites($websites){
        $this->websites=$websites;
    }
    /**
     * Returns the stores
     *
     * @return \Magento\Store\Api\Data\StoreInterface[] $stores
     */
    public function getStores(){
        return $this->stores;
    }

    /**
     * Sets the stores.
     *
     * @param \Magento\Store\Api\Data\StoreInterface[] $stores
     * @return $this
     */
    public function setStores($stores){
        $this->stores=$stores;
    }
    /**
     * Returns the source
     *
     * @return string $source
     */
    public function getSourceStock(){
        return $this->sourcestock;
    }

    /**
     * Sets the source.
     *
     * @param string $source
     * @return $this
     */
    public function setSourceStock($value){
        $this->sourcestock=$value;
    }

}