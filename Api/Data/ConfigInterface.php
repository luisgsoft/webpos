<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Gsoft\Webpos\Api\Data;

/**
 * Interface CartInterface
 * @api
 * @since 100.0.2
 */
interface ConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */


    /**
     * Returns the payments
     *
     * @return \Gsoft\Webpos\Api\Data\PaymentInterface[] payments
     */
    public function getPayments();

    /**
     * Sets the payments.
     *
     * @param \Gsoft\Webpos\Api\Data\PaymentInterface[] $payments
     * @return $this
     */
    public function setPayments($payments);

    /**
     * Returns the websites
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[] $websites
     */
    public function getWebsites();

    /**
     * Sets the payments.
     *
     * @param \Magento\Store\Api\Data\WebsiteInterface[] $websites
     * @return $this
     */
    public function setWebsites($websites);
    /**
     * Returns the stores
     *
     * @return \Magento\Store\Api\Data\StoreInterface[] $stores
     */
    public function getStores();

    /**
     * Sets the stores.
     *
     * @param \Magento\Store\Api\Data\StoreInterface[] $stores
     * @return $this
     */
    public function setStores($stores);
    /**
     * Returns the source
     *
     * @return string $source
     */
    public function getSourceStock();

    /**
     * Sets the source.
     *
     * @param string $source
     * @return $this
     */
    public function setSourceStock($value);


}
