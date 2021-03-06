<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service;

use Briqpay\Checkout\Model\Payment\Briqpay;
use Briqpay\Checkout\Model\Service\PaymentAction\ActionFactory;
use Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentProcessor
 */
class PaymentProcessor
{
    /**
     * @var PaymentAction\ActionFactory
     */
    private $actionFactory;

    /**
     * PaymentProcessor constructor.
     *
     * @param PaymentAction\ActionFactory $actionFactory
     */
    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * @param Payment $payment
     */
    public function processPayment(Payment $payment)
    {
        $isCaptured = $payment->getAdditionalInformation()['briqpay_autocapture'] ?? false;
        $paymentAction = $this->actionFactory->get($isCaptured);
        $paymentAction->process($payment);
    }
}
