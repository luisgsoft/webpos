<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model;

use Gsoft\Webpos\Api\Data\HoldInterface;
use Magento\Framework\Model\AbstractModel;

class Hold extends AbstractModel implements HoldInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Gsoft\Webpos\Model\ResourceModel\Hold::class);
    }

    /**
     * @inheritDoc
     */
    public function getHoldId()
    {
        return $this->getData(self::HOLD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setHoldId($holdId)
    {
        return $this->setData(self::HOLD_ID, $holdId);
    }

    /**
     * @inheritDoc
     */
    public function getCart()
    {
        return $this->getData(self::CART);
    }

    /**
     * @inheritDoc
     */
    public function setCart($cart)
    {
        return $this->setData(self::CART, $cart);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getTerminal()
    {
        return $this->getData(self::TERMINAL);
    }

    /**
     * @inheritDoc
     */
    public function setTerminal($terminal)
    {
        return $this->setData(self::TERMINAL, $terminal);
    }

    /**
     * @inheritDoc
     */
    public function getUser()
    {
        return $this->getData(self::USER);
    }

    /**
     * @inheritDoc
     */
    public function setUser($user)
    {
        return $this->setData(self::USER, $user);
    }

    /**
     * @inheritDoc
     */
    public function getPayed()
    {
        return $this->getData(self::PAYED);
    }

    /**
     * @inheritDoc
     */
    public function setPayed($payed)
    {
        return $this->setData(self::PAYED, $payed);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
}
