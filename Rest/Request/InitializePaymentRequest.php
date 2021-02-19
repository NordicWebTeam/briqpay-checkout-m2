<?php

namespace Briqpay\Checkout\Rest\Request;

use Magento\Framework\DataObject;

class InitializePaymentRequest
{
    /**
     * @var DataObject
     */
    private $data;

    /**
     * InitializePayment constructor.
     *
     * @param DataObject $data
     */
    public function __construct(DataObject $data)
    {
        $this->data = $data;
    }

    /**
     * @param bool $isJson
     *
     * @return string
     */
    public function getRequestBody($isJson = true)
    {
        return $isJson ? json_encode($this->data->getData()) : $this->data->getData();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->data->getItems();
    }
}
