<?php

namespace Briqpay\Checkout\Model\Payment\Command;

use Briqpay\Checkout\Rest\Exception\RefundException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class Refund implements CommandInterface
{
    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authenticationService;

    /**
     * @var \Briqpay\Checkout\Rest\Service\Refund
     */
    private $refundService;

    public function __construct(
        \Briqpay\Checkout\Rest\Service\Refund $refundService,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
        $this->refundService = $refundService;
    }

    /**
     * @param array $commandSubject
     *
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     * @throws AuthenticationException
     * @throws CommandException
     * @throws RefundException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObject $data */
        $data = $commandSubject['payment'] ?? null;

        if (! $data || !isset($commandSubject['amount'])) {
            $this->throwCommandException('Missing required argunments.');
        }

        $purchaseId = $this->getPurchaseId($data->getPayment());
        if (! $purchaseId) {
            $this->throwCommandException('Missing purchase id.');
        }

        $order = $data->getOrder();
        $payment = $data->getPayment();


        try {
            $this->authenticationService->authenticate($order->getStoreId());
            $this->refundService->refund(
                $this->authenticationService->getToken(),
                $purchaseId,
                $order->getOrderIncrementId(),
                $payment->getAuthorizationTransaction()->getTxnId(),
                $commandSubject['amount']
            );
        } catch (\Exception $e) {
            $this->throwCommandException('Can\'t make a refund for this order.');
        }
    }

    /**
     * @param InfoInterface $payment
     *
     * @return string|null
     */
    private function getPurchaseId(InfoInterface $payment)
    {
        return $payment->getAdditionalInformation()['briqpay_purchase_id'] ?? null;
    }

    /**
     * @param $text
     * @param $argc
     *
     * @throws CommandException
     */
    private function throwCommandException($text, $argc = [])
    {
        throw new CommandException(new \Magento\Framework\Phrase($text, $argc));
    }
}
