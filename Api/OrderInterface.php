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
interface OrderInterface
{



    /**
     * pay order
     *
     * @param \Gsoft\Webpos\Api\Data\OrderPaymentInterface $payment
     * @return mixed
     */
    public function pay($payment);


}
