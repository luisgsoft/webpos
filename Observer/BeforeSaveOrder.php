<?php
namespace Gsoft\Webpos\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeSaveOrder implements ObserverInterface
{


    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;
    protected $fieldsetConfig;


    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * ...
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig

    ) {
        $this->objectCopyService = $objectCopyService;
        $this->fieldsetConfig=$fieldsetConfig;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $this->quote = $observer->getEvent()->getData('quote');


        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $this->quote->debug(), $order);

        $fields_quote = $this->fieldsetConfig->getFieldset("sales_convert_quote", 'global');

        foreach ($fields_quote as $code => $node) {
            if(!empty($this->quote->getData($code))) {
                $order->setData($code, $this->quote->getData($code));

            }
        }
    }


}
