<?php
namespace Gsoft\Webpos\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;


/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected function _renderFiltersBefore()
    {

        $this->addFieldToFilter("webpos_booking", ["null" => true]);
        parent::_renderFiltersBefore();
    }
}
