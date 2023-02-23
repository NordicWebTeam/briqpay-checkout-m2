<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Payment;

use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class ResponseHandler
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param GetPaymentStatusResponse $paymentStatus
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handlePaymentStatus(
        \Magento\Quote\Model\Quote $quote,
        GetPaymentStatusResponse $paymentStatus
    ) {
        $payment = $quote->getPayment();
        $payment->unsMethodInstance();
        $payment->setMethod(\Briqpay\Checkout\Model\Payment\Briqpay::CODE);
        $briqpayPaymentMethod = $paymentStatus->getPurchasePaymentMethod();
        $this->setPaymentData($payment, [
            'briqpay_session_id' => $briqpayPaymentMethod->getSessionId(),
            'briqpay_method' => $briqpayPaymentMethod->getName(),
            'briqpay_reservation_id' => $briqpayPaymentMethod->getReservationId(),
            'briqpay_payment_status' => $paymentStatus->getState(),
            'briqpay_autocapture' => $briqpayPaymentMethod->getAutoCapture()
        ]);
    }

    /**
     * @param Payment $payment
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setPaymentData(Payment $payment, $data = []) : void
    {
        foreach ($data as $key => $value) {
            $payment->setAdditionalInformation(
                $key,
                $value
            );
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     */
    public function addAuthTransaction(\Magento\Sales\Model\Order\Payment $payment)
    {
        $payment->authorize(true, $payment->getAmountOrdered());
    }
}
