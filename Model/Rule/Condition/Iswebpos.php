<?php

namespace Gsoft\Webpos\Model\Rule\Condition;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Iswebpos extends AbstractCondition
{
    /**
     * @var Yesno
     */
    protected $sourceYesno;

    /**
     * @param Context $context
     * @param Yesno $sourceYesno
     * @param array $data
     */
    public function __construct(
        Context $context,
        Yesno   $sourceYesno,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->sourceYesno = $sourceYesno;
    }

    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'new_action_condition' => __('Es de webpos')
        ]);
        return $this;
    }

    /**
     * Get input type
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * Get value element type
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Get value select options
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData(
                'value_select_options',
                $this->sourceYesno->toOptionArray()
            );
        }
        return $this->getData('value_select_options');
    }

    /**
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $value = $this->getValueParsed();

        $iswebpos = !empty($model->getQuote()->getData("webpos_terminal"));
        return $iswebpos == $value;
    }
}
