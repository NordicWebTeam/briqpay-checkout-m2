<?php
namespace Briqpay\Checkout\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutType implements ArrayInterface
{
    /**
     * @param bool $isMultiselect
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        return [
            [
                'value' => 'B2C',
                'label' => 'B2C'
            ], [
                'value' => 'B2B',
                'label' => 'B2B'
            ]
        ];
    }
}

