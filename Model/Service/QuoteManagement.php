<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service;

use Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory;
use Briqpay\Checkout\Rest\Service\Authentication;
use Briqpay\Checkout\Rest\Service\UpdateCart;
use Magento\Quote\Model\Quote;

class QuoteManagement
{
    /**
     * @var Authentication
     */
    private $authService;

    /**
     * @var UpdateCart
     */
    private $updateCartService;

    /**
     * @var InitializePaymentRequestFactory
     */
    private $initializePaymentRequestFactory;

    /**
     * QuoteManagement constructor.
     *
     * @param Authentication $authService
     * @param UpdateCart $updateCartService
     * @param InitializePaymentRequestFactory $initializePaymentRequestFactory
     */
    public function __construct(
        Authentication $authService,
        UpdateCart $updateCartService,
        InitializePaymentRequestFactory $initializePaymentRequestFactory
    ) {
        $this->authService = $authService;
        $this->updateCartService = $updateCartService;
        $this->initializePaymentRequestFactory = $initializePaymentRequestFactory;
    }

    /**
     * @param $puchaseId
     * @param $quote
     *
     * @throws \Briqpay\Checkout\Rest\Exception\UpdateCartException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function refresh($puchaseId, Quote $quote)
    {
        $token = $this->authorize($quote->getStore()->getWebsiteId());
        $initeRequest = $this->initializePaymentRequestFactory->create($quote);

        $this->updateCartService->updateItems($initeRequest->getItems(), $puchaseId, $token);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function authorize($websiteId) : string
    {
        $this->authService->authenticate($websiteId);
        $token = $this->authService->getToken();

        return $token;
    }
}
