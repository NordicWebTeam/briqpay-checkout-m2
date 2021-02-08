<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;

interface OrderItemCollectorInterface
{
    /**
     * @param CreatePaymentSession $paymentSessionDTO
     * @param $subject {Quote | Order}
     *
     * @return mixed
     */
    public function collect(CreatePaymentSession $paymentSessionDTO, $subject);
}
