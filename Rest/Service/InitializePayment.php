<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder;
use Briqpay\Checkout\Model\Checkout\CheckoutSetup\CheckoutSetupProvider;
use Magento\Quote\Model\Quote;
use Briqpay\Checkout\Rest\{Adapter\InitializePayment as InitializePaymentAdapter,
    Exception\InitializePaymentException,
    Request\InitializePaymentRequestFactory
};

class InitializePayment
{
    /**
     * @var InitializePaymentRequestFactory
     */
    private $initializePaymentRequestFactory;


    /**
     * @var InitializePaymentAdapter
     */
    private $initializePayment;

    /**
     * @var ApiBuilder
     */
    private $apiBuilder;

    /**
     * @var CheckoutSetupProvider
     */
    private $checkoutSetupProvider;

    /**
     * @var \Briqpay\Checkout\Model\Quote\SignatureHasher
     */
    private $quoteHasher;

    /**
     * InitializePayment constructor.
     *
     * @param InitializePaymentRequestFactory $initializePaymentRequestFactory
     * @param InitializePaymentAdapter $initializePayment
     * @param ApiBuilder $apiBuilder
     */
    public function __construct(
        InitializePaymentRequestFactory $initializePaymentRequestFactory,
        InitializePaymentAdapter $initializePayment,
        CheckoutSetupProvider $checkoutSetupProvider,
        \Briqpay\Checkout\Model\Quote\SignatureHasher $quoteHasher,
        ApiBuilder $apiBuilder
    ) {
        $this->initializePaymentRequestFactory = $initializePaymentRequestFactory;
        $this->initializePayment = $initializePayment;
        $this->apiBuilder = $apiBuilder;
        $this->checkoutSetupProvider = $checkoutSetupProvider;
        $this->quoteHasher = $quoteHasher;
    }

    /**
     * @param Quote $quote
     * @param $accessToken
     *
     * @throws InitializePaymentException
     */
    public function initPayment(Quote $quote, $accessToken) : \Briqpay\Checkout\Rest\Response\InitializePaymentResponse
    {
        $initializePaymentRequest = $this->apiBuilder->collect($quote)->generateRequest();
        $quoteSignature = $this->quoteHasher->getQuoteSignature($quote);
        $quote->setBriqpayQuoteSignature($quoteSignature);

        return $this->initializePayment->initialize($initializePaymentRequest, $accessToken);
    }
}
