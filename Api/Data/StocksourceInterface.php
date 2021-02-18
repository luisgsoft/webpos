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
interface StocksourceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Returns the source
     *
     * @return string $source
     */
    public function getSourceCode();

    /**
     * Sets the source.
     *
     * @param string $source
     * @return $this
     */
    public function setSourceCode($source);
    /**
     * Returns the source
     *
     * @return int $source_id
     */
    public function getSourceId();

    /**
     * Sets the source.
     *
     * @param int $source_id
     * @return $this
     */
    public function setSourceId($source_id);


}
