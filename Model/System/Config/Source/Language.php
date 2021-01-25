<?php
namespace Briqpay\Checkout\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Language implements ArrayInterface
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
                'value' => 'English',
                'label' => 'English'
            ], [
                'value' => 'Finland',
                'label' => 'Finish'
            ], [
                'value' => 'Swedish',
                'label' => 'Swedish'
            ], [
                'value' => 'Norwegian',
                'label' => 'Norwegian'
            ], [
                'value' => 'Danish',
                'label' => 'Danish'
            ]
        ];
    }
}
