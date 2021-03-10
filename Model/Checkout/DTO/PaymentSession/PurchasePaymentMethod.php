<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\DTO\PaymentSession;

use Magento\Framework\DataObject;

class PurchasePaymentMethod
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
     * @return array|mixed|null
     */
    public function getReservationId(): ?string
    {
        return $this->data->getData('reservationid');
    }

    /**
     * @return array|mixed|null
     */
    public function getSessionId(): ?string
    {
        return $this->data->getData('sessionid');
    }

    /**
     * @return array|mixed|null
     */
    public function getPspId(): ?string
    {
        return $this->data->getData('pspid');
    }

    /**
     * @return array|mixed|null
     */
    public function getAutoCapture()
    {
        return $this->data->getData('autocapture');
    }

    /**
     * @return array|mixed|null
     */
    public function getPspName(): ?string
    {
        return $this->data->getData('pspname');
    }

    /**
     * @return array|mixed|null
     */
    public function getName(): ?string
    {
        return $this->data->getData('name');
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data->getData();
    }

}
