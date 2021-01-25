<?php

namespace Briqpay\Checkout\Model\Payment\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment\Transaction;

class Capture implements CommandInterface
{
    /**
     * @var \Briqpay\Checkout\Rest\Service\OrderDelivery
     */
    private $orderDeliveryService;

    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authenticationService;

    /**
     * Capture constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Rest\Service\OrderDelivery $orderDeliveryService,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService
    ) {
        $this->orderDeliveryService = $orderDeliveryService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param array $commandSubject
     *
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     * @throws \Briqpay\Checkout\Rest\Exception\OrderDeliveryException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObject $payment */
        $payment = $commandSubject['payment'] ?? null;
        if (! $payment) {
            return;
        }

        $storeId = $payment->getOrder()->getStoreId();
        $this->authenticationService->authenticate($storeId);
        $transactionId = $this->orderDeliveryService->orderDelivery(
            $payment->getOrder(),
            $this->authenticationService->getToken()
        );

        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $payment->getPayment();

        $orderPayment->setTransactionId($transactionId);
        $transaction = $orderPayment->addTransaction(Transaction::TYPE_AUTH);
        $transaction->setIsClosed(true);
    }
}
