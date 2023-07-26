<?php

namespace Gsoft\Webpos\Observer;

class Checkoutinit implements \Magento\Framework\Event\ObserverInterface
{

    protected $session;
    protected $logger;

    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Gsoft\Webpos\Logger\Logger $logger
    )
    {
        $this->session = $session;
        $this->logger=$logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $quote = $this->session->getQuote();
            $quote->setData("webpos_terminal", null);
            $quote->setData("webpos_alias", null);
            $quote->setData("webpos_user", null);
            $quote->setData("webpos_installments", null);
            $quote->setData("webpos_discount_percent", null);
            $quote->setData("webpos_discount_label", null);
            $quote->setData("webpos_fixed", null);
            $quote->save();
        }catch(\Exception $e){
            $this->logger->info("Error al quitar campos del webpos en el checkout");
            $this->logger->info($e->getMessage());
        }

        return $this;
    }
}
