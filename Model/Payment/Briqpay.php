<?php

namespace Briqpay\Checkout\Model\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class Briqpay implements \Magento\Payment\Model\MethodInterface
{
    const CODE = 'briqpay';

    const INFO_METHOD = 'pspname';

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $code = self::CODE;

    /**
     * @var \Briqpay\Checkout\Model\Config\ApiConfig
     */
    private $apiConfig;

    /**
     * @param Adapter                                  $adapter
     * @param Resolver                                 $resolver
     * @param ScopeConfigInterface                     $config
     */
    public function __construct(
        Adapter $adapter,
        Resolver $resolver,
        \Briqpay\Checkout\Model\Config\ApiConfig $apiConfig
    ) {
        $this->adapter = $adapter;
        $this->resolver = $resolver;
        $this->apiConfig = $apiConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($storeId = null)
    {
        return $this->apiConfig->isEnabled($storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBlockType()
    {
        return $this->adapter->getFormBlockType();
    }

    /**
     * {@inheritdoc}
     */
    public function setStore($storeId)
    {
        $this->adapter->setStore($storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->adapter->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function canOrder()
    {
        return $this->adapter->canOrder();
    }

    /**
     * {@inheritdoc}
     */
    public function canAuthorize()
    {
        return $this->adapter->canAuthorize();
    }

    /**
     * {@inheritdoc}
     */
    public function canCapture()
    {
        return $this->adapter->canCapture();
    }

    /**
     * {@inheritdoc}
     */
    public function canCapturePartial()
    {
        return $this->adapter->canCapturePartial();
    }

    /**
     * {@inheritdoc}
     */
    public function canCaptureOnce()
    {
        return $this->adapter->canCaptureOnce();
    }

    /**
     * {@inheritdoc}
     */
    public function canRefund()
    {
        return $this->adapter->canRefund();
    }

    /**
     * {@inheritdoc}
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->adapter->canRefundPartialPerInvoice();
    }

    /**
     * {@inheritdoc}
     */
    public function canVoid()
    {
        return $this->adapter->canVoid();
    }

    /**
     * {@inheritdoc}
     */
    public function canUseInternal()
    {
        return $this->adapter->canUseInternal();
    }

    /**
     * {@inheritdoc}
     */
    public function canUseCheckout()
    {
        return $this->adapter->canUseCheckout();
    }

    /**
     * {@inheritdoc}
     */
    public function canEdit()
    {
        return $this->adapter->canEdit();
    }

    /**
     * {@inheritdoc}
     */
    public function canFetchTransactionInfo()
    {
        return $this->adapter->canFetchTransactionInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->adapter->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function isGateway()
    {
        return $this->adapter->isGateway();
    }

    /**
     * {@inheritdoc}
     */
    public function isOffline()
    {
        return $this->adapter->isOffline();
    }

    /**
     * {@inheritdoc}
     */
    public function isInitializeNeeded()
    {
        return $this->adapter->isInitializeNeeded();
    }

    /**
     * {@inheritdoc}
     */
    public function canUseForCountry($country)
    {
        return $this->adapter->canUseForCountry($country);
    }

    /**
     * {@inheritdoc}
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->adapter->canUseForCurrency($currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoBlockType()
    {
        return $this->adapter->getInfoBlockType();
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoInstance()
    {
        return $this->adapter->getInfoInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->adapter->setInfoInstance($info);
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->adapter->validate();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function order(InfoInterface $payment, $amount)
    {
        $this->adapter->order($payment, $amount);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $reservationId = $payment->getAdditionalInformation()['reservationid'] ?? null;
        if (!$reservationId) {
            throw new \Magento\Payment\Gateway\Command\CommandException(
                new \Magento\Framework\Phrase('Can\'t authorize the order.')
            );
        }

        $payment->setTransactionId($reservationId);
        $transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
        $transaction->setIsClosed(false);
        $payment->addTransactionCommentsToOrder($transaction, 'Authorized Briqpay amount: ' . $amount);
        $transaction->save();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->adapter->capture($payment, $amount);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->adapter->refund($payment, $amount);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(InfoInterface $payment)
    {
        $this->adapter->cancel($payment);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function void(InfoInterface $payment)
    {
        $this->adapter->void($payment);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canReviewPayment()
    {
        return $this->adapter->canReviewPayment();
    }

    /**
     * {@inheritdoc}
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->adapter->acceptPayment($payment);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function denyPayment(InfoInterface $payment)
    {
        $this->adapter->denyPayment($payment);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->adapter->getConfigData($field, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->adapter->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function assignData(DataObject $data)
    {
        $this->adapter->assignData($data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return $this->adapter->isAvailable($quote);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->adapter->initialize($paymentAction, $stateObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPaymentAction()
    {
        return $this->adapter->getConfigPaymentAction();
    }
}
