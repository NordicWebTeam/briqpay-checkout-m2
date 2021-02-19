<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Checkout\Context\Checkout as CheckoutContext;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Magento\Checkout\Model\Session;
use Magento\TestFramework\Inspection\Exception;

class BriqpayCheckout
{
    /**
     * @var \Briqpay\Checkout\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Briqpay\Checkout\Model\Quote\QuoteManagement
     */
    private $quoteManagementService;

    /**
     * @var array
     */
    private $validators;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var PaymentManagement
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
     * @var CheckoutSession\SessionManagement
     */
    private $checkoutManagement;

    /**
     * @var \Briqpay\Checkout\Model\Config\ApiConfig
     */
    private $checkoutConfig;

    /**
     * BriqpayCheckout constructor.
     *
     * @param CheckoutContext $checkoutContext
     */
    public function __construct(CheckoutContext $checkoutContext)
    {
        $this->paymentManagement = $checkoutContext->getPaymentManagement();
        $this->customerSession = $checkoutContext->getCustomerSession();
        $this->quoteRepository = $checkoutContext->getQuoteRepository();
        $this->updateCartServiceFactory = $checkoutContext->getUpdateCartServiceFactory();
        $this->quote = $checkoutContext->getSessionManagement()->getQuote();
        $this->checkoutManagement = $checkoutContext->getSessionManagement();
        $this->quoteManagementService = $checkoutContext->getQuoteManagement();
        $this->logger = $checkoutContext->getLogger();
        $this->checkoutConfig = $checkoutContext->getCheckoutConfig();
        $this->validators = $checkoutContext->getValidators();
    }

    /**
     * @return InitializePaymentResponse
     * @throws CheckoutException
     * @throws UpdateCartException
     */
    public function initCheckout() : InitializePaymentResponse
    {
        if (!$this->checkoutConfig->isEnabled($this->quote->getStoreId())) {
            throw new \Briqpay\Checkout\Model\Checkout\CheckoutException('Briqpay checkout is not enabled');
        }

        $this->instantiateQuote();

        $purchaseId = null;
        $initPaymentBag = $purchaseId
            ? $this->updatePayment($purchaseId)
            : $this->instantiateNewPayment();

        $this->setSessionData($initPaymentBag);
        $this->validate();

        return $initPaymentBag;
    }

    /**
     * @param $purchaseId
     *
     * @return InitializePaymentResponse
     * @throws CheckoutException
     */
    private function updatePayment($purchaseId)
    {
        try {
            $this->updateItems($purchaseId, $this->quote);
            if ($this->quote->getIsChanged()) {
                $this->quoteRepository->save($this->quote);
            }

            return $this->getSessionResponse($purchaseId, $this->checkoutSession);
        } catch (\Exception $e) {
            $this->logger->error($e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getMessage());
            throw new CheckoutException('Can not load checkout at this time. Please try again later');
        }
    }

    /**
     * @return InitializePaymentResponse
     * @throws CheckoutException
     */
    private function instantiateNewPayment() : InitializePaymentResponse
    {
        try {
            $initRequest = $this->paymentManagement->initNewPayment($this->quote);
            if ($this->quote->getIsChanged()) {
                $this->quoteRepository->save($this->quote);
            }

            return $initRequest;
        } catch (\Exception $e) {
            $this->logger->error($e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getMessage());
            throw new CheckoutException($e->getMessage());
        }
    }

    /**
     * @param $purchaseId
     * @param $quote
     *
     * @throws UpdateCartException
     */
    public function updateItems($sessionId, $quote, $token)
    {
        if (!$sessionId) {
            throw new UpdateCartException('Missing purchase id');
        }

        /** @var \Briqpay\Checkout\Model\Quote\UpdateCartService $updateService */
        $updateService = $this->updateCartServiceFactory->create();
        $updateService->updateByQuote($sessionId, $quote, $token);
    }

    /**
     * @throws CheckoutException
     */
    private function instantiateQuote()
    {
        try {
            $this->quoteManagementService->instantiate($this->quote);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CheckoutException('Can not load checkout at this time. Please try again later');
        }
    }

    /**
     * @throws CheckoutException
     */
    private function validate()
    {
        foreach ($this->validators as $validator) {
            $validator->validate();
        }
    }

    /**
     * @param InitializePaymentResponse $initResponse
     */
    private function setSessionData(InitializePaymentResponse $initResponse)
    {
        $this->checkoutManagement->setSessionId($initResponse->getSessionId());
        $this->checkoutManagement->setSessionToken($initResponse->getToken());
    }

    /**
     * @param $purchaseId
     * @param Session $session
     *
     * @return InitializePaymentResponse
     * @throws CheckoutException
     */
    private function getSessionResponse($purchaseId, Session $session)
    {
        if ($purchaseId != $session->getBriqpayPurchaseId()) {
            throw new CheckoutException('Session id is not identcal');
        }

        $data = [
            'purchaseId' => $session->getBriqpayPurchaseId(),
            'expiredUtc' => $session->getBriqpayExpiredUtc(),
        ];

        return new \Briqpay\Checkout\Rest\Response\InitializePaymentResponse(
            new \Magento\Framework\DataObject($data)
        );
    }
}
