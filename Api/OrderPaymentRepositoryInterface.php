<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderPaymentRepositoryInterface
{

    /**
     * Save OrderPayment
     * @param \Gsoft\Webpos\Api\Data\OrderPaymentInterface $orderPayment
     * @return \Gsoft\Webpos\Api\Data\OrderPaymentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gsoft\Webpos\Api\Data\OrderPaymentInterface $orderPayment
    );

    /**
     * Retrieve OrderPayment
     * @param string $orderpaymentId
     * @return \Gsoft\Webpos\Api\Data\OrderPaymentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($orderpaymentId);

    /**
     * Retrieve OrderPayment matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gsoft\Webpos\Api\Data\OrderPaymentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete OrderPayment
     * @param \Gsoft\Webpos\Api\Data\OrderPaymentInterface $orderPayment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gsoft\Webpos\Api\Data\OrderPaymentInterface $orderPayment
    );

    /**
     * Delete OrderPayment by ID
     * @param string $orderpaymentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderpaymentId);
}
