<?php

namespace Briqpay\Checkout\Model\Payment\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment\Transaction;

class Capture implements CommandInterface
{

    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authenticationService;

    /**
     * @var \Briqpay\Checkout\Rest\Service\CapturePayment
     */
    private $capturePaymentService;

    /**
     * Capture constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Rest\Service\CapturePayment $capturePaymentService,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService
    )
    {
        $this->authenticationService = $authenticationService;
        $this->capturePaymentService = $capturePaymentService;
    }

    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObject $payment */
        $payment = $commandSubject['payment'] ?? null;
        if (!$payment) {
            return;
        }

        $sessionId = $payment->getPayment()->getAdditionalInformation()['sessionid'];
        $this->capturePaymentService->capture(
            $payment->getOrder(),
            $sessionId,
            $commandSubject['amount']
        );

        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $payment->getPayment();

        //$orderPayment->setTransactionId($transactionId);
        $transaction = $orderPayment->addTransaction(Transaction::TYPE_CAPTURE);
        $transaction->setIsClosed(true);
    }
}
