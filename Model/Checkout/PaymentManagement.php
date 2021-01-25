<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Quote\SignatureHasher;
use Briqpay\Checkout\Rest\Service\InitializePayment;
use Magento\Quote\Model\Quote;

class PaymentManagement
{
    /**
     * @var \Briqpay\Checkout\Rest\Service\AuthentificationInterface
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
        \Briqpay\Checkout\Rest\Service\AuthentificationInterface $authService,
        \Briqpay\Checkout\Rest\Service\InitializePayment $initPaymentService,
        SignatureHasher $hasher
    ) {
        $this->authService = $authService;
        $this->initPaymentService = $initPaymentService;
        $this->hasher = $hasher;
    }

    /**
     * @param Quote $quote
     *app/code/Briqpay/Checkout/Model/Checkout/PaymentManagement.php:41
     * @return \Briqpay\Checkout\Rest\Response\InitializePaymentResponse
     * @throws \Briqpay\Checkout\Rest\Authentification\AdapterException
     * @throws \Briqpay\Checkout\Rest\Exception\InitializePaymentException
     */
    public function initNewPayment(Quote $quote) : \Briqpay\Checkout\Rest\Response\InitializePaymentResponse
    {
        $websiteId = $quote->getStore()->getWebsiteId();

        // Authentificate website & receive access token
        $this->authService->authenticate($websiteId);
        $accessToken = $this->authService->getToken();

        $initPayment = $this->initPaymentService->initPayment($quote, $accessToken);
        $quote->setBriqpayPurchaseId($initPayment->getPurchaseId());
        $quote->setBriqpayQuoteSignature($this->hasher->getQuoteSignature($quote));

        // Instantiate Checkout and get purchase id & JWT
        return $initPayment;
    }
}
