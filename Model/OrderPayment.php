<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model;

use Gsoft\Webpos\Api\Data\OrderPaymentInterface;
use Magento\Framework\Model\AbstractModel;

class OrderPayment extends AbstractModel implements OrderPaymentInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Gsoft\Webpos\Model\ResourceModel\OrderPayment::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderpaymentId()
    {
        return $this->getData(self::ORDERPAYMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderpaymentId($orderpaymentId)
    {
        return $this->setData(self::ORDERPAYMENT_ID, $orderpaymentId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
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

    /**
     * @inheritDoc
     */
    public function getDelivered()
    {
        return $this->getData(self::DELIVERED);
    }

    /**
     * @inheritDoc
     */
    public function setDelivered($delivered)
    {
        return $this->setData(self::DELIVERED, $delivered);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getCreditmemoId()
    {
        return $this->getData(self::CREDITMEMO_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCreditmemoId($creditmemoId)
    {
        return $this->setData(self::CREDITMEMO_ID, $creditmemoId);
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
    public function getReference()
    {
        return $this->getData(self::REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setReference($reference)
    {
        return $this->setData(self::REFERENCE, $reference);
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
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIncrementId($increment_id)
    {
        return $this->setData(self::INCREMENT_ID, $increment_id);
    }
    /**
     * @inheritDoc
     */
    public function getWebposBooking()
    {
        return $this->getData(self::BOOKING);
    }

    /**
     * @inheritDoc
     */
    public function setWebposBooking($value)
    {
        return $this->setData(self::BOOKING, $value);
    }
}
