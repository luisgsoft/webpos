<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Gsoft\Webpos\Plugin\Quote;


/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item
{


    public function aroundRepresentProduct($subject, $procedeed,  $product)
    {

        if(!empty($product->getData("webpos_item_id"))) return false;
        return $procedeed($product);
    }


}
