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
interface CatalogInterface
{

    /**
     * Get simples from configurable
     *
     * @api
     * @param string $sku
     * * @param string $store_id
     * @return mixed[]
     */
    public function getSimples($sku, $store_id);

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getProductList($searchCriteria);




}
