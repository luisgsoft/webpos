<?php
namespace Gsoft\Webpos\Model\ResourceModel\Invoice\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as OriginalCollection;


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
