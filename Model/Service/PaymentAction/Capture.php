<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service\PaymentAction;

use Magento\Sales\Model\Order\Payment;

class Capture implements PaymentActionInterface
{
    /**
     * @param Payment $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(Payment $payment) : void
    {
        $payment->capture();
    }
}
