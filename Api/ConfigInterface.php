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
interface ConfigInterface
{

    /**
     * Get webpos config options
     * @param int $website_id
     * @api
     * @return \Gsoft\Webpos\Api\Data\ConfigInterface
     */
    public function getConfig($website_id);


}
