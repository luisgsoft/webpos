<?php
namespace Gsoft\Webpos\Model\ResourceModel\Creditmemo\Grid;

use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection as OriginalCollection;


/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    protected function _renderFiltersBefore()
    {
        $this->addFieldToFilter("main_table.webpos_booking", ["null" => true]);
        parent::_renderFiltersBefore();

    }
}
