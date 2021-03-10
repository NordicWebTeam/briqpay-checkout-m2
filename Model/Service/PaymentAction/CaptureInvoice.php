<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service\PaymentAction;

use Magento\Sales\Model\Order\Payment;

class CaptureInvoice implements PaymentActionInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    private $invoiceSender;

    /**
     * CaptureInvoice constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param Payment $payment
     *
     * @return mixed|void
     */
    public function process(Payment $payment) : void
    {
        $order = $payment->getOrder();
        $payment->authorize(true, $order->getBaseTotalDue());
        $this->invoicePayment($order, $payment->getTransactionId());
        $payment->capture();

        $order
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);

        $this->orderRepository->save($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function invoicePayment(\Magento\Sales\Model\Order $order, $transactionId)
    {
        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->pay();
            $invoice->register();
            $invoice->setTransactionId($transactionId);
            $invoice->save();
            $transactionSave = $this->transaction->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();

            $this->invoiceSender->send($invoice);
            $order->setIsInProcess(true);
            $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                ->setIsCustomerNotified(true);
        } catch (\Exception $e) {
            // We cannot terminate transaction
        }
    }
}
