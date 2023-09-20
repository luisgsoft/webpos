<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api\Data;

interface OrderPaymentInterface
{

    const AMOUNT = 'amount';
    const ORDER_ID = 'order_id';
    const NAME = 'name';
    const CODE = 'code';
    const CREDITMEMO_ID = 'creditmemo_id';
    const DELIVERED = 'delivered';
    const ORDERPAYMENT_ID = 'orderpayment_id';
    const CREATED_AT = 'created_at';
    const LABEL = 'label';
    const REFERENCE = 'reference';
    const TERMINAL = 'terminal';
    const USER = 'user';
    const INCREMENT_ID = 'increment_id';
    const BOOKING = 'webpos_booking';
    /**
     * Get orderpayment_id
     * @return string|null
     */
    public function getOrderpaymentId();

    /**
     * Set orderpayment_id
     * @param string $orderpaymentId
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setOrderpaymentId($orderpaymentId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setOrderId($orderId);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setCode($code);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setLabel($label);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setName($name);

    /**
     * Get delivered
     * @return string|null
     */
    public function getDelivered();

    /**
     * Set delivered
     * @param string $delivered
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setDelivered($delivered);

    /**
     * Get amount
     * @return string|null
     */
    public function getAmount();

    /**
     * Set amount
     * @param string $amount
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setAmount($amount);

    /**
     * Get creditmemo_id
     * @return string|null
     */
    public function getCreditmemoId();

    /**
     * Set creditmemo_id
     * @param string $creditmemoId
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setCreditmemoId($creditmemoId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setCreatedAt($createdAt);
    /**
     * Get reference
     * @return string|null
     */
    public function getReference();

    /**
     * Set name
     * @param string reference
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setReference($reference);
    /**
     * Get terminal
     * @return string|null
     */
    public function getTerminal();

    /**
     * Set terminal
     * @param string $terminal
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setTerminal($terminal);
    /**
     * Get terminal
     * @return string|null
     */
    public function getUser();

    /**
     * Set terminal
     * @param string $user
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setUser($user);
    /**
     * Get terminal
     * @return string|null
     */
    public function getIncrementId();

    /**
     * Set terminal
     * @param string $increment_id
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setIncrementId($increment_id);
    /**
     * Get webpos_booking
     * @return int|null
     */
    public function getWebposBooking();

    /**
     * Set terminal
     * @param int $value
     * @return \Gsoft\Webpos\OrderPayment\Api\Data\OrderPaymentInterface
     */
    public function setWebposBooking($value);
}
