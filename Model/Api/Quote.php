<?php

namespace Gsoft\Webpos\Model\Api;

use Gsoft\Webpos\Api\QuoteInterface;

class Quote implements QuoteInterface
{
    protected $hlp;
    protected $manager;


    public function __construct(
        \Gsoft\Webpos\Helper\Data $helper
    )
    {

        $this->hlp = $helper;
        if ($this->hlp ->isVersionGreatherOrEqual("2.3")) {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V3\Model\Api\Quote");
        } else {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V2\Model\Api\Quote");

        }

    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createQuote($data)
    {

        return $this->manager->createQuote($data);
    }

    public function getInfoCoupon($couponCode)
    {

        return $this->manager->getInfoCoupon($couponCode);
    }

    public function prepareQuote($data)
    {
        return $this->manager->prepareQuote($data);
    }

    public function refundOrder($data){
        return $this->manager->refundOrder($data);
    }

}
