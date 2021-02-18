<?php
/**
 * Created by PhpStorm.
 * User: user444
 * Date: 15/05/2020
 * Time: 11:19
 */

namespace Gsoft\Webpos\Model\ResourceModel;

class Stockreservation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('webpos_stock_reservation', 'id');
    }

}