<?php

namespace Briqpay\Checkout\Model\System\Config\Source;

class Locale
{
    /**
     * Swedish, Norway, Danish Kronor
     * @var array $allowedCurrencies
     */
    protected $allowedCurrencies = [
        'SEK',
        'NOK',
        'DKK',
        'EUR'
    ];

    /**
     * @var array
     */
    protected $allowedCountries = [
        'SE',
        'NO',
        'DK',
        'FI'
    ];

    /**
     * @return array
     */
    public function getAllowedCurrencies()
    {
        return $this->allowedCurrencies;
    }

    /**
     * @return array
     */
    public function getAllowedCountries()
    {
        return $this->allowedCountries;
    }
}
