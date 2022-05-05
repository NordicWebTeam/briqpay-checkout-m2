<?php

namespace Briqpay\Checkout\Rest\Response;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\PurchasePaymentMethod;
use Magento\Framework\DataObject;

/**
 * Class GetPaymentStatusResponse
 * @method getCountry() : ?string
 */
class GetPaymentStatusResponse implements ResponseInterface
{
    /**
     * @var DataObject
     */
    private $data;

    /**
     * @var PurchasePaymentMethod
     */
    private $purchasePaymentMethod;

    /**
     * InitializePayment constructor.
     *
     * @param DataObject $data
     */
    public function __construct(DataObject $data)
    {
        $this->data = $data;
        $this->purchasePaymentMethod = new PurchasePaymentMethod(
            new DataObject($data->getData('purchasepaymentmethod') ?: [])
        );
    }

    /**
     * @return array|mixed|null
     */
    public function getBillingAddress()
    {
        return $this->data->getData('billingaddress');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = '')
    {
        return $this->data->getData($key);
    }

    /**
     * @return array|mixed|null
     */
    public function getSessionId()
    {
        return $this->data->getData('sessionid');
    }

    /**
     * @return array|mixed|null
     */
    public function getState()
    {
        return $this->data->getData('state');
    }

    public function getCart(): DataObject
    {
        return new DataObject($this->data->getData('cart'));
    }

    /**
     * @return array|mixed|null
     */
    public function getPurchasePaymentMethod(): PurchasePaymentMethod
    {
        return $this->purchasePaymentMethod;
    }

    /**
     * @return bool
     */
    public function isPurchaseComplete()
    {
        return $this->getState() == 'purchasecomplete';
    }

    /**
     * @param $method
     * @param $args
     *
     * @return bool|mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                return $this->getData(
                    strtolower(substr($method, 3, strlen($method)))
                );
        }
    }
}
