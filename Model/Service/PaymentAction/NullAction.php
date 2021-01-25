<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service\PaymentAction;

use Magento\Sales\Model\Order\Payment;

class NullAction implements PaymentActionInterface
{
    /**
     * @param Payment $payment
     *
     * @return void
     */
    public function process(Payment $payment) : void
    {
        // void
    }
}
