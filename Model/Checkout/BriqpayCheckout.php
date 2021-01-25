<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Checkout\CheckoutSession\AttributeSchema;
use Briqpay\Checkout\Model\Checkout\Context\Checkout as CheckoutContext;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Magento\Checkout\Model\Session;

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
     * BriqpayCheckout constructor.
     *
     * @param CheckoutContext $checkoutContext
     */
    public function __construct(CheckoutContext $checkoutContext)
    {
        $this->paymentManagement = $checkoutContext->getPaymentManagement();
        $this->checkoutSession = $checkoutContext->getCheckoutSession();
        $this->customerSession = $checkoutContext->getCustomerSession();
        $this->quoteRepository = $checkoutContext->getQuoteRepository();
        $this->updateCartServiceFactory = $checkoutContext->getUpdateCartServiceFactory();
        $this->quote = $checkoutContext->getCheckoutSession()->getQuote();
        $this->quoteManagementService = $checkoutContext->getQuoteManagement();
        $this->logger = $checkoutContext->getLogger();
        $this->validators = $checkoutContext->getValidators();
    }

    /**
     * @throws CheckoutException
     */
    public function initCheckout() : InitializePaymentResponse
    {
        $this->instantiateQuote();

        $purchaseId = $this->getPurchaseIdIfValid();
        $initPaymentBag = $purchaseId
            ? $this->updatePayment($purchaseId)
            : $this->instantiateNewPayment();

        $this->setSessionData($initPaymentBag);
        $this->validate();

        return $initPaymentBag;
    }

    /**
     * @return null if expired
     * @return mixed
     */
    private function getPurchaseIdIfValid()
    {
        $purchaseId = $this->checkoutSession->getData(AttributeSchema::PURCHASE_ID);
        $expiredUtc = $this->checkoutSession->getData(AttributeSchema::EXPIRED_UTC);
        if (! $purchaseId || !$expiredUtc) {
            return null;
        }

        $expiredUtcDate = \DateTime::createFromFormat('Y-m-d\TH:i:s+', $expiredUtc);
        $currentDate = new \DateTime(gmdate('Y-m-d\TH:i:s.u'));

        return $currentDate < $expiredUtcDate ? $purchaseId : null;
    }


    /**
     * @param $purchaseId
     *
     * @return InitializePaymentResponse
     * @throws CheckoutException
     * @throws UpdateCartException
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
     * @param null $purchaseId
     *
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
            throw new CheckoutException('Can not load checkout at this time. Please try again later');
        }
    }

    /**
     * @param $purchaseId
     * @param $quote
     *
     * @throws UpdateCartException
     */
    public function updateItems($purchaseId, $quote)
    {
        if (!$purchaseId) {
            throw new UpdateCartException('Missing purchase id');
        }

        /** @var \Briqpay\Checkout\Model\Quote\UpdateCartService $updateService */
        $updateService = $this->updateCartServiceFactory->create();
        $updateService->updateByQuote($purchaseId, $quote);
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
        if ($initResponse->getPurchaseId() != $this->checkoutSession->getData(AttributeSchema::PURCHASE_ID)) {
            $this->checkoutSession->setData(AttributeSchema::PURCHASE_ID, $initResponse->getPurchaseId());
            $this->checkoutSession->setData(AttributeSchema::JWT, $initResponse->getJwt());
            $this->checkoutSession->setData(AttributeSchema::EXPIRED_UTC, $initResponse->getExpiredUtc());
        }
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
            'jwt' => $session->getBriqpayJwt(),
        ];

        return new \Briqpay\Checkout\Rest\Response\InitializePaymentResponse(
            new \Magento\Framework\DataObject($data)
        );
    }
}
