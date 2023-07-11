<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HoldRepositoryInterface
{

    /**
     * Save Hold
     * @param \Gsoft\Webpos\Api\Data\HoldInterface $hold
     * @return \Gsoft\Webpos\Api\Data\HoldInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Gsoft\Webpos\Api\Data\HoldInterface $hold
    );

    /**
     * Retrieve Hold
     * @param string $holdId
     * @return \Gsoft\Webpos\Api\Data\HoldInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($holdId);

    /**
     * Retrieve Hold matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Gsoft\Webpos\Api\Data\HoldSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Hold
     * @param \Gsoft\Webpos\Api\Data\HoldInterface $hold
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Gsoft\Webpos\Api\Data\HoldInterface $hold
    );

    /**
     * Delete Hold by ID
     * @param string $holdId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($holdId);
}

