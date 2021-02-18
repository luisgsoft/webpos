<?php

namespace Gsoft\Webpos\Model\Payment;

/**
 * Pay In Store payment method model
 */
class Card extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'webposcard';

    public function canUseInternal()
    {
        return true;
    }
    public function canUseCheckout()
    {
        return true;
    }
}