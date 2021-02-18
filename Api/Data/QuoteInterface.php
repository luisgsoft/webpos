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
interface QuoteInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */


    /**#@-*/

    /**
     * Returns the cart/quote ID.
     *
     * @return int Cart/quote ID.
     */
    public function getQuote();

    /**
     * Sets the cart/quote ID.
     *
     * @param int $id
     * @return $this
     */
    public function setQuote($quote);


}
