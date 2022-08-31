<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote;

use Briqpay\Checkout\Rest\Exception\UpdateCartException;
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
     * @param $purchaseId
     * @param Quote $quote
     *
     * @throws UpdateCartException
     */
    public function updateByQuote($purchaseId, Quote $quote, $token)
    {
        try {
            $this->apiBuilder->collect($quote);
            $updateRequest = $this->apiBuilder->generateRequest();
            $data = $updateRequest->getRequestBody(false);

            $this->updateCart->updateItems($data, $purchaseId, $token);
        } catch (\Exception $e) {
            throw new UpdateCartException(
                $e->getPrevious()->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
