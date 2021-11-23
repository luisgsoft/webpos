<?php

namespace Gsoft\Webpos\Model\Total;
/**
 * Class Custom
 * @package Gsoft\Webpos\Model\Total
 */
class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $scopeConfig;
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {

        $this->setCode('webposdiscount');
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig=$scopeConfig;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {

        parent::collect($quote, $shippingAssignment, $total);
        if (!$quote->getData("items_qty") > 0 || !$total->getData("subtotal") > 0) return;

        $address = $shippingAssignment->getShipping()->getAddress();

        $label = $quote->getWebposDiscountLabel();
        $TotalAmount = 0;
        if (!$quote->getWebposDiscountFixed() > 0 && !$quote->getWebposDiscountPercent() > 0) return;


        $tax_percent = 0;
        $TotalAmount_without_tax = $TotalAmountTaxed=0;
        $units = 0;

        if ($quote->getWebposDiscountPercent() > 0) {



            foreach ($quote->getAllVisibleItems() as $item) {

                $discount = $item->getRowTotal() * $quote->getWebposDiscountPercent() * 0.01;
                $TotalAmount_without_tax+=$discount;
                $discounttax=$item->getRowTotalInclTax() * $quote->getWebposDiscountPercent() * 0.01;
                $TotalAmountTaxed +=$discounttax;

                $item->setDiscountAmount($item->getDiscountAmount()+$discounttax);
                $item->setBaseDiscountAmount($item->getBaseDiscountAmount()+$discounttax);
                $item->setDiscountPercent($quote->getWebposDiscountPercent());


            }
            $TotalAmount=$TotalAmountTaxed;

        } else {
            //total de descuento con iva
            $TotalAmount = $quote->getWebposDiscountFixed();
            $TotalAmountTaxed=$TotalAmount;
            $TotalAmount_without_tax = $TotalAmount;
            /**/
            //vamos a averiguar que iva debe tener
            $qty=0;
            foreach ($quote->getAllVisibleItems() as $item) {
                $units++;
                $qty+=$item->getQty();
                $tax_percent += $item->getTaxPercent();

            }
            $tax_percent = $tax_percent / $units;
            if ($tax_percent > 0) {

                if(!$this->scopeConfig->getValue("tax/calculation/discount_tax")){
                    $TotalAmount_without_tax = $TotalAmount;
                    $TotalAmountTaxed = $TotalAmount*(1+$tax_percent*0.01);

                }else {
                    $TotalAmount_without_tax = $TotalAmount / (1 + ($tax_percent * 0.01));
                    $TotalAmountTaxed=$TotalAmount;
                }

            }
            $remain=0;
            //vamos a obtener en porcentaje cuanto descuento se aplica
            if($this->scopeConfig->getValue("tax/calculation/discount_tax")) {
                $remain = $TotalAmountTaxed;
            }else{
                $remain = $TotalAmount_without_tax;
            }


            $percent_discount=($remain*100)/$total->getSubtotal();


            //vamos a repartir el descuento proporcionalmente entre cada producto
            foreach ($quote->getAllVisibleItems() as $item) {
                if($remain==0) break;
                $discount=$item->getRowTotal()*$percent_discount*0.01;
                if(($remain - $discount)<0) $discount=$remain;
                $remain-=$discount;
                $item->setDiscountAmount($item->getDiscountAmount()+$discount);
                $item->setBaseDiscountAmount($item->getBaseDiscountAmount()+$discount);
            }

        }

        $discountAmount = "-" . abs($TotalAmount);
        $appliedCartDiscount = 0;


        if (!empty($total->getDiscountAmount())) {


            $discountAmount = $total->getDiscountAmount() + $discountAmount;
            $desc=!empty($total->getDiscountDescription())?$total->getDiscountDescription():__("Descuento de carrito");

            $label=$desc.", ".$label;
        } else {
            $total->setDiscountDescription($label);
        }


        $total->setDiscountDescription($label);
        $total->setDiscountAmount($discountAmount);
        $total->setBaseDiscountAmount($discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotal() - abs($TotalAmount_without_tax));
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal()  - abs($TotalAmount_without_tax));


        /*print_r($total->debug());
        die();*/
        if ($appliedCartDiscount > 0) {

            $total->addTotalAmount($this->getCode(), -1*abs($TotalAmountTaxed) - $appliedCartDiscount);
            $total->addBaseTotalAmount($this->getCode(), -1*abs($TotalAmountTaxed) - $appliedCartDiscount);
        } else {

            $total->addTotalAmount($this->getCode(), -1*abs($TotalAmountTaxed));
            $total->addBaseTotalAmount($this->getCode(), -1*abs($TotalAmountTaxed));

        }
        return $this;

    }

    public
    function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {

        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $description = $total->getDiscountDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }

}
