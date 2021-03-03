<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder;
use Briqpay\Checkout\Rest\Adapter\InitializePayment as InitializePaymentAdapter;
use Briqpay\Checkout\Rest\Exception\InitializePaymentException;
use Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory;
use Magento\Quote\Model\Quote;

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
        \Briqpay\Checkout\Model\Quote\SignatureHasher $quoteHasher,
        ApiBuilder $apiBuilder
    ) {
        $this->initializePaymentRequestFactory = $initializePaymentRequestFactory;
        $this->initializePayment = $initializePayment;
        $this->apiBuilder = $apiBuilder;
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
        $this->apiBuilder->collect($quote);
        $initializePaymentRequest = $this->apiBuilder->generateRequest();

        return $this->initializePayment->initialize($initializePaymentRequest, $accessToken);
    }
}
