<?php

namespace Gsoft\Webpos\Plugin;

class QuotePaymentPlugin
{
    /**
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param array $data
     * @return array
     */
    public function beforeImportData(\Magento\Quote\Model\Quote\Payment $subject, array $data)
    {
        if (array_key_exists('additional_data', $data)) {

            $subject->setAdditionalInformation("webpos",$data['additional_data']);

        }

        return [$data];
    }
}