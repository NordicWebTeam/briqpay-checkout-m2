<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Checkout\Context\Checkout as CheckoutContext;
use Briqpay\Checkout\Model\Checkout\Validation\CheckoutValidatorInterface;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Briqpay\Checkout\Setup\QuoteSchema;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\CartInterface;

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
     * @var CheckoutValidatorInterface []
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
     * @var \Briqpay\Checkout\Model\Quote\SignatureHasher
     */
    private $signatureHasher;

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
        $this->signatureHasher = $checkoutContext->getSignatureHasher();
    }

    /**
     * @return InitializePaymentResponse
     * @throws CheckoutException
     */
    public function initCheckout() : InitializePaymentResponse
    {
        if (!$this->checkoutConfig->isEnabled($this->quote->getStoreId())) {
            throw new CheckoutException('Briqpay checkout is not enabled');
        }

        $this->validate();
        $this->instantiateQuote();

        // If no session id, we instantiate a new payment
        // and save it in session.
        if ($this->getSessionId()) {
            $initPaymentBag = $this->instantiateNewPayment();
            $this->setSessionData($initPaymentBag);

            return $initPaymentBag;
        }

        // Else we do update of existing payment session
        try {
            $this->updatePayment(...$this->getSessionArgs());
        } catch (CheckoutException $e) {
            // In case if update is failed -> init new payment
            try {
                $initPaymentBag = $this->instantiateNewPayment();
            } catch (CheckoutException $e) {
                // We should clear current session
                $this->checkoutManagement->clear();
                throw $e;
            }
            $this->setSessionData($initPaymentBag);

            return $initPaymentBag;
        }
        // Updating of the session doesn't return anything.
        // So we can fetch InitializePaymentResponse data from previous session
        return InitializePaymentResponse::createFromArray($this->checkoutManagement->getSessionData());
    }

    /**
     * @param $sessionId
     * @param $token
     *
     * @throws CheckoutException
     */
    private function updatePayment($sessionId, $token)
    {
        $storedCartSignature = $this->quote->getBriqpayCartSignature();
        $newSignature = $this->signatureHasher->getQuoteSignature($this->quote);

        // If cart is the same, we should not update
        if ($newSignature == $storedCartSignature) {
            return;
        }

        try {
            $this->updateItems($sessionId, $this->quote, $token);
            if ($this->quote->getIsChanged()) {
                $this->quoteRepository->save($this->quote);
            }
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
            $this->calculateSignature();
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
     * @param CartInterface $cart
     * @param InitializePaymentResponse $initRequest
     */
    private function assignAttributes(CartInterface $cart, InitializePaymentResponse $initRequest)
    {
        if ($sessionId = $initRequest->getSessionId()) {
            $cart->setData(QuoteSchema::SESSION_ID, $sessionId);
            $cart->getExtensionAttributes()->setBriqpaySessionId($sessionId);
        }

        if ($sessionToken = $initRequest->getToken()) {
            $cart->setData(QuoteSchema::SESSION_TOKEN, $sessionToken);
            $cart->getExtensionAttributes()->setBriqpaySessionToken($sessionToken);
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
        $this->checkoutManagement->setSnippet($initResponse->getSnippet());
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
            throw new CheckoutException('Session id is not identical');
        }

        $data = [
            'purchaseId' => $session->getBriqpayPurchaseId(),
            'expiredUtc' => $session->getBriqpayExpiredUtc(),
        ];

        return new \Briqpay\Checkout\Rest\Response\InitializePaymentResponse(
            new \Magento\Framework\DataObject($data)
        );
    }

    /**
     * @return array
     */
    private function getSessionArgs()
    {
        return [$this->checkoutManagement->getSessionId(), $this->checkoutManagement->getSessionToken()];
    }

    /**
     * @return string|null
     */
    private function getSessionId()
    {
        return $this->checkoutManagement->getSessionId();
    }

    /**
     * Add cart signrature
     */
    private function calculateSignature()
    {
        $signature = $this->signatureHasher->getQuoteSignature($this->quote);
        $this->quote->getExtensionAttributes()->setBriqpayCartSignature($signature);
        $this->quote->setData(QuoteSchema::CART_SIGNATURE, $signature);
    }
}
