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

        $sessionId = $payment->getPayment()->getAdditionalInformation()['briqpay_session_id'];
        if (!$sessionId) {
            throw new \Exception('Session ID is not available for this payment.');
        }

        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $payment->getPayment();

        // Detect if payment has been captured
        $isCaptured = $orderPayment->getAdditionalInformation()['briqpay_autocapture'] ?? null;
        if (!$isCaptured) {
            $this->capturePaymentService->capture(
                $payment->getOrder(),
                $sessionId,
                $commandSubject['amount']
            );
        }

        $reservationId = $orderPayment->getAdditionalInformation()['briqpay_reservation_id'] ?? null;

        $transaction = $orderPayment->addTransaction(Transaction::TYPE_CAPTURE);
        $transaction->setTransactionId($reservationId);
        $transaction->setIsClosed(true);
    }
}
