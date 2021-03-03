<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\Context;

use Briqpay\Checkout\Logger\Logger;
use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Model\Quote\QuoteManagement;
use Briqpay\Checkout\Model\Quote\SignatureHasher;
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
     * @var SessionManagement
     */
    private $sessionManagement;

    /**
     * @var \Briqpay\Checkout\Model\Config\ApiConfig
     */
    private $checkoutConfig;

    /**
     * @var SignatureHasher
     */
    private $signatureHasher;

    /**
     * CheckoutContext constructor.
     *
     * @param \Briqpay\Checkout\Model\Checkout\PaymentManagement $paymentManagement
     * @param QuoteManagement $quoteManagement
     * @param \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory $updateCartServiceFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param CustomerSession $customerSession
     * @param SessionManagement $sessionManagement
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $validators
     */
    public function __construct(
        \Briqpay\Checkout\Model\Checkout\PaymentManagement $paymentManagement,
        QuoteManagement $quoteManagement,
        \Briqpay\Checkout\Model\Quote\UpdateCartServiceFactory $updateCartServiceFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        CustomerSession $customerSession,
        SessionManagement $sessionManagement,
        \Psr\Log\LoggerInterface $logger,
        \Briqpay\Checkout\Model\Config\ApiConfig $checkoutConfig,
        SignatureHasher $signatureHasher,
        $validators = []
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->quoteManagement = $quoteManagement;
        $this->validators = $validators;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->updateCartServiceFactory = $updateCartServiceFactory;
        $this->quoteRepository = $quoteRepository;
        $this->sessionManagement = $sessionManagement;
        $this->checkoutConfig = $checkoutConfig;
        $this->signatureHasher = $signatureHasher;
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
    public function getLogger(): \Psr\Log\LoggerInterface
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

    /**
     * @return SessionManagement
     */
    public function getSessionManagement(): SessionManagement
    {
        return $this->sessionManagement;
    }

    /**
     * @return \Briqpay\Checkout\Model\Config\ApiConfig
     */
    public function getCheckoutConfig(): \Briqpay\Checkout\Model\Config\ApiConfig
    {
        return $this->checkoutConfig;
    }

    /**
     * @return SignatureHasher
     */
    public function getSignatureHasher(): SignatureHasher
    {
        return $this->signatureHasher;
    }
}
