<?php
/**
 * Copyright © asd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gsoft\Webpos\Model\Payment;

class Webpos_installment extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "webpos_installment";
    protected $_isOffline = true;
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    public function canUseInternal()
    {
        return true;
    }
    public function canUseCheckout()
    {
        return true;
    }


}

