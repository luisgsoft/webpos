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
    /**
     * book order
     *
     * @param int $order_id
     * @param int $status
     * @return mixed
     */
    public function book($order_id, $status);

}
