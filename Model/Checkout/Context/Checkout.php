<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\Context;

use Briqpay\Checkout\Logger\Logger;
use Briqpay\Checkout\Model\Quote\QuoteManagement;
use Briqpay\Checkout\Rest\Service\AuthenticationFactory;
use Briqpay\Checkout\Rest\Service\InitializePayment;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Result\PageFactory;

class Checkout
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var AuthenticationFactory
     */
    private $authenticationFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var InitializePayment
     */
    private $initializePaymentService;

    /**
     * @var array
     */
    private $validators;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Briqpay\Checkout\Model\Checkout\PaymentManagement
     */
    private $paymentManagement;

    /**
     * @var \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory
     */
    private $updateCartServiceFactory;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * CheckoutContext constructor.
     *
     * @param \Briqpay\Checkout\Model\Checkout\PaymentManagement $paymentManagement
     * @param QuoteManagement $quoteManagement
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param Logger $logger
     * @param array $validators
     */
    public function __construct(
        \Briqpay\Checkout\Model\Checkout\PaymentManagement $paymentManagement,
        QuoteManagement $quoteManagement,
        Session $checkoutSession,
        \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory $updateCartServiceFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        CustomerSession $customerSession,
        Logger $logger,
        $validators = []
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->quoteManagement = $quoteManagement;
        $this->validators = $validators;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->updateCartServiceFactory = $updateCartServiceFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }

    /**
     * @return AuthenticationFactory
     */
    public function getAuthenticationFactory(): AuthenticationFactory
    {
        return $this->authenticationFactory;
    }

    /**
     * @return Session
     */
    public function getCheckoutSession(): Session
    {
        return $this->checkoutSession;
    }

    /**
     * @return QuoteManagement
     */
    public function getQuoteManagement(): QuoteManagement
    {
        return $this->quoteManagement;
    }

    /**
     * @return InitializePayment
     */
    public function getInitializePaymentService(): InitializePayment
    {
        return $this->initializePaymentService;
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @return CustomerSession
     */
    public function getCustomerSession(): CustomerSession
    {
        return $this->customerSession;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return \Briqpay\Checkout\Model\Checkout\PaymentManagement
     */
    public function getPaymentManagement(): \Briqpay\Checkout\Model\Checkout\PaymentManagement
    {
        return $this->paymentManagement;
    }

    /**
     * @return \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory
     */
    public function getUpdateCartServiceFactory(): \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory
    {
        return $this->updateCartServiceFactory;
    }

    /**
     * @return \Magento\Quote\Model\QuoteRepository
     */
    public function getQuoteRepository(): \Magento\Quote\Model\QuoteRepository
    {
        return $this->quoteRepository;
    }
}
