<?php

namespace Briqpay\Checkout\Rest\Response;

use Magento\Framework\DataObject;

/**
 * @method getCheckoutSite()
 * @method getTotalPrice()
 */
class GetPaymentStatusResponse implements ResponseInterface
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
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = '')
    {
        return $this->data->getData($key);
    }

    /**
     * @return array|null
     */
    public function getBillingAddress() : ?array
    {
        $paymentData = $this->getPaymentData();

        return $paymentData['invoicingAddress'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getShippingAddress() : ?array
    {
        $paymentData = $this->getPaymentData();

        return $paymentData['deliveryAddress'] ?? null;
    }

    /**
     * @return mixed
     */
    public function getPurchaseId()
    {
        return $this->getData('purchaseId');
    }

    /**
     * @return mixed|null
     */
    public function getCustomerToken()
    {
        $statusData = $this->getPaymentData();

        return $statusData['step']['customerToken'] ?? null;
    }


    /**
     * @return mixed
     */
    public function getPaymentMode()
    {
        return $this->getData('mode');
    }

    /**
     *
     */
    public function getSelectedPaymentMethod()
    {
        $methods = $this->getData('paymentMethods');

        return $methods['selectedPayment']['type'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getUserInputData()
    {
        $paymentData = $this->getPaymentData();

        return $paymentData['userInputs'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getEmail() : ?string
    {
        $paymentData = $this->getPaymentData();

        return $paymentData['userInputs']['email'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPaymentStatus() : ?string
    {
        $statusData = $this->getPaymentData();

        return $statusData['step']['current'] ?? null;
    }

    /**
     * @return mixed
     */
    public function getPaymentData()
    {
        return $this->getData('mode') == 'B2C'
            ? $this->getData('b2C')
            : $this->getData('b2B');
    }

}
