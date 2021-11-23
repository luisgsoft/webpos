<?php

namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveShipment implements ObserverInterface
{


    protected $hlp;
    protected $manager;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(

        \Gsoft\Webpos\Helper\Data $helper
    )
    {
        $this->hlp = $helper;
        if ($this->hlp ->isVersionGreatherOrEqual("2.3")) {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V3\Observer\SaveShipment");
        } else {
            $this->manager = $this->hlp->loadObject("\Gsoft\Webpos\Version\V2\Observer\SaveShipment");

        }


    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this->manager->execute($observer);
    }

}
