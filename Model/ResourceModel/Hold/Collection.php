<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model\ResourceModel\Hold;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'hold_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Gsoft\Webpos\Model\Hold::class,
            \Gsoft\Webpos\Model\ResourceModel\Hold::class
        );
    }
}

