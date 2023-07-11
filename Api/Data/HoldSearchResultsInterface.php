<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api\Data;

interface HoldSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Hold list.
     * @return \Gsoft\Webpos\Api\Data\HoldInterface[]
     */
    public function getItems();

    /**
     * Set cart list.
     * @param \Gsoft\Webpos\Api\Data\HoldInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

