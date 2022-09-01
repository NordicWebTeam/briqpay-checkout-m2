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
     * @var \Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder
     */
    private $apiBuilder;

    /**
     * QuoteRepository constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Rest\Service\Authentication $authService,
        \Briqpay\Checkout\Rest\Service\UpdateCart $updateCart,
        \Briqpay\Checkout\Model\Checkout\ApiBuilder\ApiBuilder $apiBuilder
    ) {
        $this->authService = $authService;
        $this->updateCart = $updateCart;
        $this->apiBuilder = $apiBuilder;
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
