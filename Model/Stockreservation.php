<?php
/**
 * Created by PhpStorm.
 * User: user444
 * Date: 15/05/2020
 * Time: 11:19
 */

namespace Gsoft\Webpos\Model;

/**
 * @method \Gsoft\Webpos\Model\ResourceModel\Stockreservation getResource()
 * @method \Gsoft\Webpos\Model\ResourceModel\Stockreservation\Collection getCollection()
 */
class Stockreservation extends \Magento\Framework\Model\AbstractModel implements \Gsoft\Webpos\Api\Data\StockreservationInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'gsoft_webpos_stockreservation';
    protected $_cacheTag = 'gsoft_webpos_stockreservation';
    protected $_eventPrefix = 'gsoft_webpos_stockreservation';

    protected function _construct()
    {
        $this->_init('Gsoft\Webpos\Model\ResourceModel\Stockreservation');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}