<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Quote\SignatureHasher;
use Briqpay\Checkout\Rest\Authentification\AdapterException;
use Briqpay\Checkout\Rest\Exception\InitializePaymentException;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Briqpay\Checkout\Rest\Service\AuthentificationInterface;
use Briqpay\Checkout\Rest\Service\InitializePayment;
use Magento\Quote\Model\Quote;

class PaymentManagement
{
    /**
     * @var AuthentificationInterface
     */
    private $authService;

    /**
     * @var InitializePayment
     */
    private $initPaymentService;

    /**
     * @var SignatureHasher
     */
    private $hasher;

    /**
     * CheckoutManagement constructor.
     */
    public function __construct(
        AuthentificationInterface $authService,
        InitializePayment $initPaymentService,
        SignatureHasher $hasher
    )
    {
        $this->authService = $authService;
        $this->initPaymentService = $initPaymentService;
        $this->hasher = $hasher;
    }

    /**
     * Instantiate Checkout and get purchase ID & JWT
     *
     * @param Quote $quote
     *
     * @return InitializePaymentResponse
     * @throws AdapterException
     * @throws InitializePaymentException
     */
    public function initNewPayment(Quote $quote): InitializePaymentResponse
    {
        $websiteId = $quote->getStore()->getWebsiteId();
        $accessToken = $this->authService->authenticate($websiteId);
        $initPayment = $this->initPaymentService->initPayment($quote, $accessToken);

        return $initPayment;
    }
}
