<?php

namespace Gsoft\Webpos\Model\Api;

use Gsoft\Webpos\Api\OrderInterface;

class Order implements OrderInterface
{
    protected $hlp;
    protected $manager;


    public function __construct(
        \Gsoft\Webpos\Helper\Data $helper
    )
    {

        $this->hlp = $helper;
        if ($this->hlp ->isVersionGreatherOrEqual("2.3")) {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V3\Model\Api\Order");
        } else {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V2\Model\Api\Order");

        }

    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function pay($data)
    {

        return $this->manager->pay($data);
    }



}
