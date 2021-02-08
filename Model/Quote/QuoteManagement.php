<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote;

use Briqpay\Checkout\Model\Payment\Briqpay;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Setup\QuoteSchema;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;

class QuoteManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CustomerDataAssigner\Factory
     */
    private $customerDataAssignerFactory;

    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutSetupConfig;

    /**
     * QuoteManagement constructor.
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Briqpay\Checkout\Model\Quote\CustomerDataAssigner\Factory $customerDataAssignerFactory,
        \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerDataAssignerFactory = $customerDataAssignerFactory;
        $this->checkoutSetupConfig = $checkoutSetupConfig;
    }

    /**
     * @param Quote $quote
     */
    public function instantiate(Quote $quote) : void
    {
        if (! $quote->isVirtual()) {
            $this->initShippingMethod($quote);
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * @param Quote $quote
     *
     * @return bool|string|void
     */
    public function initShippingMethod(Quote $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if (! $shippingAddress->getCountryId() || $shippingAddress->getCountryId() != $this->checkoutSetupConfig->getDefaultCountry()) {
            $targetCountry = $this->checkoutSetupConfig->getDefaultCountry();
            $this->changeQuoteCountry($targetCountry, $quote);
        }

        $shipping = $quote
            ->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        $allRates = $shipping->getAllShippingRates();
        if (!count($allRates)) {
            return false;
        }

        $rates = [];
        foreach ($allRates as $rate) {
            /** @var $rate Quote\Address\Rate  **/
            $rates[$rate->getCode()] = $rate->getCode();
        }

        $method = $shipping->getShippingMethod();
        if ($method && isset($rates[$method])) {
            return;
        }

        // Fallback, use first shipping method found
        $rate = $allRates[0];
        $method = $rate->getCode();
        $shipping->setShippingMethod($method);
    }

    /**
     * @param $country
     * @param Quote $quote
     */
    private function changeQuoteCountry($country, Quote $quote) : void
    {
        $blankAddress = $this->getBlankAddress($country);
        $quote->getBillingAddress()->addData($blankAddress);
        $quote->getShippingAddress()->addData($blankAddress);
    }

    /**
     * @param Quote $quote
     * @param GetPaymentStatusResponse $paymentStatusResponse
     */
    public function setDataFromResponse(Quote $quote, array $paymentStatusResponse) : void
    {
        $this->setCustomerEmail($quote, $paymentStatusResponse);
        $this->setShippingData($quote, $paymentStatusResponse);
//        $this->setBillingData($quote, $paymentStatusResponse);
        $this->setCustomerData($quote);

        $payment = $quote->getPayment();
        if (!$payment->getMethod() || $payment->getMethod() != Briqpay::CODE) {
            $payment->unsMethodInstance()->setMethod(Briqpay::CODE);
        }

        $paymentData = new DataObject([
            QuoteSchema::PURCHASE_ID => $paymentStatusResponse['sessionid'],
            'country_id'             => $quote->getShippingAddress()->getCountryId(),
        ]);

        $method = $payment->getMethodInstance();
        $method->assignData($paymentData);
        $this->quoteRepository->save($quote);
    }

    /**
     * @param Quote $quote
     */
    private function setCustomerData(Quote $quote)
    {
        $customer = $quote->getCustomer();
        $checkoutType = ($customer && $customer->getId())
            ? CustomerDataAssignerInterface::TYPE_CUSTOMER
            : CustomerDataAssignerInterface::TYPE_GUEST;

        $customerDataAssigner = $this->customerDataAssignerFactory->create($checkoutType);
        $customerDataAssigner->assignData($quote);
    }

    /**
     * @param Quote $quote
     * @param GetPaymentStatusResponse $paymentStatusResponse
     */
    private function setCustomerEmail(Quote $quote, array $paymentStatusResponse) : void
    {
        if ($email = $paymentStatusResponse['billingaddress']['email']) {
            $quote->setCustomerEmail($email);
        }
    }

    /**
     * @param Quote $quote
     * @param GetPaymentStatusResponse $paymentStatusResponse
     */
    private function setShippingData(Quote $quote, array $paymentStatusResponse) : void
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingData = $paymentStatusResponse['billingaddress'];

        // Using for fallback
       // $userData = $paymentStatusResponse->getUserInputData();

//        $street = $shippingData['address1'] ?? null . $shippingData['address2'] ?? null;
        $data = [
            'firstname' => $shippingData['firstname'],
            'lastname' => $shippingData['lastname'],
            'telephone' => $shippingData['cellno'],
            'email' => $shippingData['email'],
            'street' => $shippingData['streetaddress'],
            'city' => $shippingData['city'] ?? null,
            'postcode' => $shippingData['zip'] ?? ($userData['zip'] ?? null),
            'country_id' => $paymentStatusResponse['country']
        ];

        $shippingAddress->addData($data);
        $shippingAddress->setShouldIgnoreValidation(true);
        $shippingAddress->save();

        $billing = $quote->getBillingAddress();
        $billing->addData($data);
        $billing->setShouldIgnoreValidation(true);
        $billing->save();

    }

    /**
     * @param Quote $quote
     * @param GetPaymentStatusResponse $paymentStatusResponse
     */
    private function setBillingData(Quote $quote, GetPaymentStatusResponse $paymentStatusResponse) : void
    {
        $billingAddress = $quote->getBillingAddress();
        $billingData = $paymentStatusResponse->getBillingAddress();
        $userData = $paymentStatusResponse->getUserInputData();

        $street = $billingData['address1'] ?? null . $billingData['address2'] ?? null;
        $data = [
            'firstname' => $billingData['firstName'] ?? null,
            'lastname' => $billingData['lastName'] ?? null,
            'telephone' => $billingData['firstName'] ?? null,
            'email' => $userData['email'] ?? null,
            'street' => $street,
            'city' => $billingData['city'] ?? null,
            'postcode' => $billingData['zip'] ?? null,
            'country_id' => $billingData['country'] ?? null
        ];

        $billingAddress->addData($data);
        $billingAddress->setShouldIgnoreValidation(true);
    }

    /**
     * @param $country
     * @return array
     */
    public function getBlankAddress($country)
    {
        $blankAddress = [
            'customer_address_id' => 0,
            'save_in_address_book' => 0,
            'same_as_billing' => 0,
            'street' => '',
            'city' => '',
            'postcode' => '',
            'region_id' => '',
            'country_id' => $country
        ];

        return $blankAddress;
    }
}
