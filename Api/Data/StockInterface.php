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
interface StockInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */


    /**
     * Returns the qty
     *
     * @return float qty
     */
    public function getQty();

    /**
     * Sets the payments.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Returns the status
     *
     * @return int $status
     */
    public function getStatus();

    /**
     * Sets the $status.
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Returns the sources
     *
     * @return \Gsoft\Webpos\Api\Data\StocksourceInterface[] $source
     */
    public function getSources();

    /**
     * Sets the sources.
     *
     * @param \Gsoft\Webpos\Api\Data\StocksourceInterface[] $sources
     * @return $this
     */
    public function setSources($sources);


}
