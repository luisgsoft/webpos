<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api\Data;

interface OrderPaymentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get OrderPayment list.
     * @return \Gsoft\Webpos\Api\Data\OrderPaymentInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Gsoft\Webpos\Api\Data\OrderPaymentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
