<?php

namespace Gsoft\Webpos\Plugin\Order\Validation;

class CanRefund{


    public function afterValidate($context, $res, $entity)
    {
        //para poder hacer devoluciones sobre devoluciones, ya que magento no permite devoluciones con importe 0
        return [];
    }
}
