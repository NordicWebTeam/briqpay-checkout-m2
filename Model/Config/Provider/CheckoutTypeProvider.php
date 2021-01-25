<?php

namespace Briqpay\Checkout\Model\Config\Provider;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;

/**
 * Class CheckoutTypeProvider
 *
 * @package Briqpay\Checkout\Model\Config\Provider
 */
class CheckoutTypeProvider
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutSetupConfig;

    /**
     * ShippingCountries constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig,
        Session $checkoutSession
    )
    {
        $this->checkoutSetupConfig = $checkoutSetupConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getData(Quote $quote = null)
    {
        $type = $this->checkoutSetupConfig->getCheckoutType();

        return $type == 'B2C' ? $this->getConfigB2C($quote) : $this->getConfigB2B($quote);
    }

    /**
     * @return array
     */
    private function getConfigB2B(Quote $quote)
    {

    }

    /**
     * @return array
     */
    private function getConfigB2C(Quote $quote)
    {
        $billing = $quote->getShippingAddress();
        $shipping = $quote->getShippingAddress();

        return [
            'b2C' => [
                "customerToken" => null,
                "invoicingAddress" => [
                    "address1" => $billing->getStreetLine(0),
                    "address2" => $billing->getStreetLine(1),
                    "zip" => $billing->getPostcode(),
                    "city" => $billing->getCity(),
                    "country" => $billing->getCountry(),
                    "firstName" => $quote->getCustomer()->getFirstname(),
                    "lastName" => $quote->getCustomer()->getLastname()
                ],
                "deliveryAddress" => [
                    "address1" => $shipping->getStreetLine(0),
                    "address2" => $shipping->getStreetLine(1),
                    "zip" => $shipping->getPostcode(),
                    "city" => $shipping->getCity(),
                    "country" => $shipping->getCountry(),
                    "firstName" => $quote->getCustomer()->getFirstname(),
                    "lastName" => $quote->getCustomer()->getLastname(),
                    "type" => "Default"
                ],
                "userIputs" => [
                    "phone" => $billing->getTelephone(),
                    "email" => $quote->getCustomerEmail()
                ]]
        ];
    }
}
