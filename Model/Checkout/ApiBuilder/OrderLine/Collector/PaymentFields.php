<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;

class PaymentFields implements OrderItemCollectorInterface
{
    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutConfig;

    /**
     * @var \Briqpay\Checkout\Model\Config\UrlBuilder
     */
    private $urlBuilder;

    /**
     * ItemsCollector constructor.
     *
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutConfig
     * @param \Briqpay\Checkout\Model\Config\UrlBuilder $urlBuilder
     */
    public function __construct(
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutConfig,
        \Briqpay\Checkout\Model\Config\UrlBuilder $urlBuilder
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->checkoutConfig = $checkoutConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param CreatePaymentSession $paymentSession
     * @param \Magento\Quote\Model\Quote $subject
     *
     * @return mixed|void
     */
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        $paymentSession->setCurrency($this->checkoutConfig->getCurrency());
        $paymentSession->setLocale($this->checkoutConfig->getCheckoutLanguage());
        $paymentSession->setCountry($this->checkoutConfig->getDefaultCountry());
        $paymentSession->setReference([
            "ref" => "quote_{$subject->getId()}"
        ]);
        $paymentSession->setMerchantConfig([
            "maxamount" => $this->checkoutConfig->isBriqpayMaxamount(),
            "creditscoring" => $this->checkoutConfig->isBriqpayCreditscoring()
        ]);

        $paymentSession->setMerchantUrls([
            'terms' => $this->urlBuilder->getTermsUrl(),
            'notifications' => $this->urlBuilder->getNotificationUrl(),
            'redirecturl' => $this->urlBuilder->getRedirectUrl()
        ]);
//
//        // Test block
//        $address = new CreatePaymentSession\Address();
//        $address->setCompanyname("Company AB");
//        $address->setFirstname("Andriy");
//        $address->setLastname("Kravets");
//        $address->setStreetaddress("Kingstreet 1 B");
//        $address->setZip("24224");
//        $address->setCity("Kingcity");
//        $address->setCellno("+46703334441");
//        $address->setEmail("youremail@mail.com");
//
//        $paymentSession->setBillingAddress($address);
//        $paymentSession->setShippingAddress($address);
    }
}
