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
                'value' => 'en-gb',
                'label' => 'English'
            ], [
                'value' => 'fi-fi',
                'label' => 'Finish'
            ], [
                'value' => 'se-se',
                'label' => 'Swedish'
            ], [
                'value' => 'nb-no',
                'label' => 'Norwegian'
            ], [
                'value' => 'da-dk',
                'label' => 'Danish'
            ]
        ];
    }
}
