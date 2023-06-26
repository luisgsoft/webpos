<?php

namespace Gsoft\Webpos\Plugin\SalesRule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Action\FileNameCondition;

class Iswebpos
{
    /**
     * @param Combine $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetNewChildSelectOptions(
        Combine $subject,
        array $result
    ): array
    {
        $groupLabel = __('Cart Attribute');
        $conditionAdded = false;

        foreach ($result as &$condition) {

            if (isset($condition['value'], $condition['label'])
                && is_array($condition['value'])
                && $condition['label']->getText() === $groupLabel->getText()
            ) {

                $condition['value'][] = $this->getCondition();
                $conditionAdded = true;
                break;
            }
        }

        if (!$conditionAdded) {
            // if group of "Cart Item Attribute" not founded then add condition separately
            $result[] = $this->getCondition();
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getCondition(): array
    {
        return [
            'value' => \Gsoft\Webpos\Model\Rule\Condition\Iswebpos::class,
            'label' => __('Realizado en el webpos')
        ];
    }
}
