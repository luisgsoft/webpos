<?php

namespace Gsoft\Webpos\Model\Payment;

/**
 * Pay In Store payment method model
 */
class Cash extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'webposcash';
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;
    protected $_isOffline = true;

    public function canUseInternal()
    {
        return true;
    }
    public function canUseCheckout()
    {
        return true;
    }
}
