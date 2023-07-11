<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Api\Data;

interface HoldInterface
{

    const STORE_ID = 'store_id';
    const PAYED = 'payed';
    const NAME = 'name';
    const CART = 'cart';
    const HOLD_ID = 'hold_id';
    const CREATED_AT = 'created_at';
    const TERMINAL = 'terminal';
    const USER = 'user';

    /**
     * Get hold_id
     * @return string|null
     */
    public function getHoldId();

    /**
     * Set hold_id
     * @param string $holdId
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setHoldId($holdId);

    /**
     * Get cart
     * @return string|null
     */
    public function getCart();

    /**
     * Set cart
     * @param string $cart
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setCart($cart);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get terminal
     * @return string|null
     */
    public function getTerminal();

    /**
     * Set terminal
     * @param string $terminal
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setTerminal($terminal);

    /**
     * Get user
     * @return string|null
     */
    public function getUser();

    /**
     * Set user
     * @param string $user
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setUser($user);

    /**
     * Get payed
     * @return string|null
     */
    public function getPayed();

    /**
     * Set payed
     * @param string $payed
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setPayed($payed);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setStoreId($storeId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Gsoft\Webpos\Hold\Api\Data\HoldInterface
     */
    public function setName($name);
}
