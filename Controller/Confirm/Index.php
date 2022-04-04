<?php
namespace Briqpay\Checkout\Controller\Confirm;

use Briqpay\Checkout\Model\Checkout\Context\Callback as CallbackContext;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Magento\Checkout\Controller\Action;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;

class Index extends Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManager;

    /**
     * @var \Briqpay\Checkout\Model\Quote\QuoteManagement
     */
    private $quoteManager;

    /**
     * @var \Briqpay\Checkout\Model\Payment\ResponseHandler
     */
    private $quoteResponseHandler;

    /**
     * @var \Briqpay\Checkout\Model\Service\PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var \Briqpay\Checkout\Rest\Service\SessionManagement
     */
    private $sessionManagement;

    /**
     * @var \Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement
     */
    private $checkoutSessionManager;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param Callback $callbackContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        CallbackContext $callbackContext
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );

        $this->checkoutSession = $callbackContext->getCheckoutSession();
        $this->cartManager = $callbackContext->getCartManager();
        $this->quoteManager = $callbackContext->getQuoteManager();
        $this->quoteResponseHandler = $callbackContext->getResponseHandler();
        $this->paymentProcessor = $callbackContext->getPaymentProcessor();
        $this->sessionManagement = $callbackContext->getSessionManagement();
        $this->checkoutSessionManager = $callbackContext->getCheckoutSessionManager();
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {
        $sessionId = $this->checkoutSessionManager->getSessionId();
        if (!$sessionId) {
            $this->messageManager->addErrorMessage('Session is not found, please check again.');
            return $this->_redirect('briqpay');
        }

        try {
            $paymentResponse = $this->getPaymentStatus();
            $quote = $this->checkoutSessionManager->getQuote();
            $this->quoteResponseHandler->handlePaymentStatus($quote, $paymentResponse);
            $this->quoteManager->setDataFromResponse($quote, $paymentResponse);

            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->cartManager->submit($quote);
            $this->checkoutSession
                ->setLastQuoteId($quote->getId())
                ->setLastSuccessQuoteId($quote->getId())
                ->clearHelperData();

            $this->paymentProcessor->processPayment($order->getPayment());
            $this->dispatchPostEvents($order, $quote);

            /**
             * a flag to set that there will be redirect to third party after confirmation
             */
            $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();

            $this->checkoutSession
                ->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());

            $this->checkoutSessionManager->clear();
            $this->checkoutSessionManager->setBriqpayPaymentMethod($paymentResponse->getPurchasePaymentMethod());

            return $this->_redirect('briqpay/order/success');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Can not instantiate your payment request. Please try again.');
            return $this->_redirect('checkout/cart');
        }
    }

    /**
     * @return GetPaymentStatusResponse
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     */
    private function getPaymentStatus(): GetPaymentStatusResponse
    {
        return $this->sessionManagement->readSession(
            $this->checkoutSessionManager->getSessionId(),
            $this->checkoutSessionManager->getSessionToken()
        );
    }

    /**
     * Dispatch post events
     *
     * @param $order
     * @param $quote
     */
    private function dispatchPostEvents($order, $quote)
    {
        $this->_eventManager->dispatch(
            'checkout_type_onepage_save_order_after',
            ['order' => $order, 'quote' => $quote]
        );

        $this->_eventManager->dispatch(
            'briqpay_checkout_complete',
            ['order' => $order, 'quote' => $quote]
        );
    }
}
