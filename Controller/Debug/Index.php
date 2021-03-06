<?php
namespace Briqpay\Checkout\Controller\Debug;

use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Model\Checkout\Context\Callback as CallbackContext;
use Briqpay\Checkout\Rest\Adapter\GetPaymentStatus;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Rest\Service\Authentication;
use Magento\Checkout\Controller\Action;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var GetPaymentStatus
     */
    private $paymentStatusService;

    /**
     * @var Authentication
     */
    private $authService;

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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Quote\Model\QuoteValidator
     */
    private $quoteValidator;

    /**
     * @var SessionManagement
     */
    private $checkoutSessionManager;

    /**
     * @var SessionManagement
     */
    private $sessionManagement;

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
        CallbackContext $callbackContext,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Briqpay\Checkout\Rest\Service\SessionManagement $sessionManagement
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );

        $this->pageFactory = $callbackContext->getPageFactory();
        $this->paymentStatusService = $callbackContext->getPaymentStatusService();
        $this->authService = $callbackContext->getAuthService();
        $this->checkoutSession = $callbackContext->getCheckoutSession();
        $this->cartManager = $callbackContext->getCartManager();
        $this->quoteManager = $callbackContext->getQuoteManager();
        $this->orderRepository = $callbackContext->getOrderRepository();
        $this->quoteValidator = $quoteValidator;
        $this->checkoutSessionManager = $callbackContext->getCheckoutSessionManager();
        $this->sessionManagement = $sessionManagement;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws \Briqpay\Checkout\Rest\Authentification\AdapterException
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->checkoutSessionManager->getSessionId()) {
            die('No session id available');
        }
        print_r($this->getPaymentStatus()->getData());
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
}
