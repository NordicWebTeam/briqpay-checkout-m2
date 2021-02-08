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
     * @inheritDoc
     */
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        $paymentSession->setCurrency('SEK');
        $paymentSession->setLocale('se-se');
        $paymentSession->setCountry('SE');
        $paymentSession->setReference([
            "reference1" => "string",
            "reference2" => "string"
        ]);
        $paymentSession->setOrgNr('559249-5336');
        $paymentSession->setMerchantConfig([
            "maxamount"     => true,
            "creditscoring" => false
        ]);

        $paymentSession->setMerchantUrls([
            'terms'         => $this->urlBuilder->getTermsUrl(),
            'notifications' => $this->urlBuilder->getNotificationUrl(),
            'redirecturl'   => $this->urlBuilder->getRedirectUrl()
        ]);

        // Test block
        $address = new CreatePaymentSession\Address();
        $address->setCompanyname("Company AB");
        $address->setFirstname("Andriy");
        $address->setLastname("Kravets");
        $address->setStreetaddress("Kingstreet 1 B");
        $address->setZip("24224");
        $address->setCity("Kingcity");
        $address->setCellno("+46703334441");
        $address->setEmail("youremail@mail.com");

        $paymentSession->setBillingAddress($address);
        $paymentSession->setShippingAddress($address);
    }
}
