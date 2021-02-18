<?php
/**
 * Created by PhpStorm.
 * User: user444
 * Date: 15/05/2020
 * Time: 11:19
 */

namespace Gsoft\Webpos\Model\ResourceModel\Stockreservation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';


    protected function _construct()
    {
        $this->_init('Gsoft\Webpos\Model\Stockreservation', 'Gsoft\Webpos\Model\ResourceModel\Stockreservation');
    }

}