<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Gsoft\Webpos\Api;

/**
 * Tools interface.
 * @api
 */
interface QuoteInterface
{



    /**
     * Create quote
     *
     * @param mixed $quote
     * @return mixed
     */
    public function createQuote($quote);

    /**
     * Get discount coupon
     *
     * @param mixed $coupon_code
     * @return mixed
     */
    public function getInfoCoupon($coupon_code);

    /**
     * Create order
     *
     * @param mixed $quote
     * @return mixed
     */
    public function prepareQuote($quote);



}
