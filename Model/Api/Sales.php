<?php

namespace Gsoft\Webpos\Model\Api;

use Gsoft\Webpos\Api\SalesInterface;

class Sales implements SalesInterface
{
    protected $hlp;
    protected $manager;


    public function __construct(
        \Gsoft\Webpos\Helper\Data $helper
    )
    {

        $this->hlp = $helper;
        if ($this->hlp ->isVersionGreatherOrEqual("2.3")) {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V3\Model\Api\Sales");
        } else {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V2\Model\Api\Sales");

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



}