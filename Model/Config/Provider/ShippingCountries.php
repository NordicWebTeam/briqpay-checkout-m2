<?php

namespace Briqpay\Checkout\Model\Config\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ShippingCountries
{
    private const XML_BRIQPAY_SHIPPING_ALLOWED_COUNTRIES = 'unifaun_udc/udc/allowed_countries';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $countyList = [
        'dk' => 'Denmark',
        'fi' => 'Finland',
        'no' => 'Norway',
        'se' => 'Sweden',
    ];

    /**
     * ShippingCountries constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        $countries = [];
        $allowedCountries = $this->scopeConfig->getValue(self::XML_BRIQPAY_SHIPPING_ALLOWED_COUNTRIES);

        if (isset($this->countyList[$allowedCountries])) {
            $countries[strtoupper($allowedCountries)] = $this->countyList[$allowedCountries];
        }

        return $countries;
    }
}
