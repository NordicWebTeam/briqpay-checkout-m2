<?php

namespace Briqpay\Checkout\Rest\Response;

use Magento\Framework\DataObject;

class AuthentificationResponse implements ResponseInterface
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
    public function getToken()
    {
        return $this->data->getData('token');
    }

    /**
     * @return mixed
     */
    public function getTokenExpirationUtc()
    {
        return $this->data->getData('tokenExpirationUtc');
    }
}
