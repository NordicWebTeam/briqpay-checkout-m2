<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\DTO\PaymentSession;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession\Address;

/**
 * Class CreatePaymentSession
 *
 * @package Briqpay\Checkout\Model\Checkout\DTO\PaymentSession
 */
class CreatePaymentSession
{
    /**
     * @var
     */
    private $currency;

    /**
     * @var
     */
    private $locale;

    /**
     * @var
     */
    private $country;

    /**
     * @var
     */
    private $amount;

    /**
     * @var
     */
    private $cart = [];

    /**
     * @var
     */
    private $merchantUrls;

    /**
     * @var
     */
    private $merchantConfig = [];

    /**
     * @var
     */
    private $reference = [];

    /**
     * @var
     */
    private $orgNr;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var Address
     */
    private $shippingAddress;

    /**
     * CreatePaymentSession constructor.
     */
    public function __construct()
    {
        // $this->billingAddress = new Address();
        // $this->shippingAddress = new Address();
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param array $item
     */
    public function addCartItem(array $item): void
    {
        $this->cart[] = $item;
    }

    /**
     * @return mixed
     */
    public function getMetchantUrls()
    {
        return $this->merchantUrls;
    }

    /**
     * @param mixed $merchantUrls
     */
    public function setMerchantUrls(array $metchantUrls): void
    {
        $this->merchantUrls = $metchantUrls;
    }

    /**
     * @return mixed
     */
    public function getMerchantConfig()
    {
        return $this->merchantConfig;
    }

    /**
     * @param mixed $merchantConfig
     */
    public function setMerchantConfig(array $merchantConfig): void
    {
        $this->merchantConfig = $merchantConfig;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     */
    public function setReference(array $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getOrgNr()
    {
        return $this->orgNr;
    }

    /**
     * @param mixed $orgNr
     */
    public function setOrgNr($orgNr): void
    {
        $this->orgNr = $orgNr;
    }

    /**
     * @return mixed
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $address): void
    {
        $this->billingAddress = $address;
    }

    /**
     * @return mixed
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        if (isset($this->currency)) {
            $result['currency'] = $this->currency;
        }

        if (isset($this->locale)) {
            $result['locale'] = $this->locale;
        }

        if (isset($this->country)) {
            $result['country'] = $this->country;
        }

        if (isset($this->amount)) {
            $result['amount'] = $this->amount;
        }

        if (isset($this->cart)) {
            $result['cart'] = $this->cart;
        }

        if (isset($this->merchantUrls)) {
            $result['merchanturls'] = $this->merchantUrls;
        }

        if (isset($this->merchantConfig)) {
            $result['merchantconfig'] = $this->merchantConfig;
        }

        if (isset($this->reference)) {
            $result['reference'] = $this->reference;
        }

        if (isset($this->orgNr)) {
            $result['orgnr'] = $this->orgNr;
        }

        if (isset($this->billingAddress)) {
            $result['billingaddress'] = $this->billingAddress->toArray();
        }

        if (isset($this->shippingAddress)) {
            $result['shippingaddress'] = $this->shippingAddress->toArray();
        }


        return $result;
    }
}
