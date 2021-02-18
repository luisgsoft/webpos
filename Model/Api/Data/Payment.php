<?php

namespace Gsoft\Webpos\Model\Api\Data;

class Payment implements \Gsoft\Webpos\Api\Data\PaymentInterface{

    protected $code;
    protected $label;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCode(){
            return $this->code;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setCode($code){
        $this->code=$code;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getLabel(){
        return $this->label;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setLabel($label){
        $this->label=$label;
    }
}