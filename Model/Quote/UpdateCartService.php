<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote;

use Magento\Quote\Model\Quote;

class UpdateCartService
{
    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authService;

    /**
     * @var \Briqpay\Checkout\Rest\Service\UpdateCart
     */
    private $updateCart;

    /**
     * @var \Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory
     */
    private $initializePaymentRequestFactory;

    /**
     * @var \Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder
     */
    private $apiBuilder;

    /**
     * @var SignatureHasher
     */
    private $quoteHasher;

    /**
     * QuoteRepository constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Rest\Service\Authentication $authService,
        \Briqpay\Checkout\Rest\Service\UpdateCart $updateCart,
        \Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder $apiBuilder,
        \Briqpay\Checkout\Model\Quote\SignatureHasher $quoteHasher,
        \Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory $initializePaymentRequestFactory
    ) {
        $this->authService = $authService;
        $this->updateCart = $updateCart;
        $this->initializePaymentRequestFactory = $initializePaymentRequestFactory;
        $this->apiBuilder = $apiBuilder;
        $this->quoteHasher = $quoteHasher;
    }

    /**
     * @param Quote $quote
     * @param $purchaseId
     *
     * @throws \Briqpay\Checkout\Rest\Exception\UpdateCartException
     */
    public function updateByQuote($purchaseId, Quote $quote)
    {
        try {
            $quoteSignature = $this->quoteHasher->getQuoteSignature($quote);
            if ($quoteSignature == $quote->getBriqpayQuoteSignature()) {
                return;
            }

            $items = $this->apiBuilder->collect($quote)->getOrderLines();
            $this->authService->authenticate($quote->getStoreId());
            $token = $this->authService->getToken();

            $this->updateCart->updateItems($items, $purchaseId, $token);
            $quote->setBriqpayQuoteSignature($quoteSignature);
        } catch (\Exception $e) {
            throw new \Briqpay\Checkout\Rest\Exception\UpdateCartException(
                $e->getPrevious()->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param Quote $quote
     *
     * @return string
     */
    public function getQuoteSignature(Quote $quote)
    {
        $shippingMethod = null;
        $countryId = null;
        if (!$quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $countryId = $shippingAddress->getCountryId();
            $shippingMethod = $shippingAddress->getShippingMethod();
        }

        $billingAddress = $quote->getBillingAddress();
        $info = [
            'currency'=> $quote->getQuoteCurrencyCode(),
            'shipping_method' => $shippingMethod,
            'shipping_country' => $countryId,
            'billing_country' => $billingAddress->getCountryId(),
            'payment' => $quote->getPayment()->getMethod(),
            'subtotal'=> sprintf("%.2f", round($quote->getBaseSubtotal(), 2)),
            'total'=> sprintf("%.2f", round($quote->getBaseGrandTotal(), 2)),
            'items'=> []
        ];

        foreach ($quote->getAllVisibleItems() as $item) {
            $info['items'][$item->getId()] = sprintf("%.2f", round($item->getQty()*$item->getBasePriceInclTax(), 2));
        }
        ksort($info['items']);

        return md5(serialize($info));
    }
}
