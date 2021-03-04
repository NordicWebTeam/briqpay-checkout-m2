<?php

namespace Briqpay\Checkout\Model\Payment\Command;

use Briqpay\Checkout\Rest\Adapter\CancelAdapter;
use Briqpay\Checkout\Rest\Exception\RefundException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class Cancel implements CommandInterface
{
    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authenticationService;

    /**
     * @var \Briqpay\Checkout\Rest\Service\Refund
     */
    private $refundService;

    /**
     * @var CancelAdapter
     */
    private $cancelAdapter;

    public function __construct(
        CancelAdapter $cancelAdapter,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
        $this->cancelAdapter = $cancelAdapter;
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
        if (! $data) {
            $this->throwCommandException('Cannot cancel the order.');
        }

        $purchaseId = $this->getPurchaseId($data->getPayment());
        if (! $purchaseId) {
            $this->throwCommandException('Cannot cancel the order.');
        }

        try {
            $storeId = $data->getOrder()->getStoreId();
            $this->cancelAdapter->cancel(
                $this->authenticationService->authenticate($storeId),
                $purchaseId,
                'Canceled by admin'
            );
        } catch (\Exception $e) {
            $this->throwCommandException('Cannot cancel the order.');
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
