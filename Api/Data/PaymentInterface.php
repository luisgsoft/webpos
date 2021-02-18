<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Gsoft\Webpos\Api\Data;

/**
 * Interface CartInterface
 * @api
 * @since 100.0.2
 */
interface PaymentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */




    /**
     * Returns the code
     *
     * @return string code
     */
    public function getCode();

    /**
     * Sets the code.
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Returns the label
     *
     * @return string label
     */
    public function getLabel();

    /**
     * Sets the label.
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);


}
