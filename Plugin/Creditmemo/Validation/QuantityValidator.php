<?php

namespace Gsoft\Webpos\Plugin\Creditmemo\Validation;

class QuantityValidator {

    public function afterValidate($subject, $res, $entity){

        if(!empty($res)){

            foreach($res as $k=> $row){
                if($row == __('The credit memo\'s total must be positive.')){
                    unset($res[$k]);
                    break;
                }
            }

        }
        return $res;
    }


}
