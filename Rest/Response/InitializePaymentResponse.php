<?php

namespace Briqpay\Checkout\Rest\Response;

use Magento\Framework\DataObject;

class InitializePaymentResponse implements ResponseInterface
{
    /**
     * @var DataObject
     */
    private $data;

    /**
     *
     */
    public static function createFromArray(array $data)
    {
        return new self(new DataObject($data));
    }

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
     * @param null $key
     *
     * @return mixed
     */
    public function getData($key = null)
    {
        return $this->data->getData();
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->data->getData('sessionid');
    }

    /**
     * @return mixed
     */
    public function getSnippet()
    {
        return $this->data->getData('snippet');
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->data->getData('token');
    }
}
