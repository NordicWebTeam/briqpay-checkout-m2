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
    public function getPurchaseId()
    {
        return $this->data->getData('purchaseId');
    }

    /**
     * @return mixed
     */
    public function getJwt()
    {
        return $this->data->getData('jwt');
    }

    /**
     * @return mixed
     */
    public function getExpiredUtc()
    {
        return $this->data->getData('expiredUtc');
    }
}
