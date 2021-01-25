<?php
namespace Briqpay\Checkout\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Country implements ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var array
     */
    private $countryMap = [];

    /**
     * Country constructor.
     *
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param Locale $locale
     */
    public function __construct(
        \Magento\Directory\Model\Config\Source\Country $country,
        Locale $locale
    ) {
        $this->locale = $locale;
        $this->country = $country;
    }

    /**
     * @param bool $isMultiselect
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        $this->initCountryMap();

        $locales = $this->locale->getAllowedCountries();
        $return = [];

        if (!$isMultiselect) {
            $return[] = ['value'=>'', 'label'=> ''];
        }

        $mappedCountries = [];
        foreach ($locales as $countryCode) {
            $label = $this->getCountryLabelByCode($countryCode);
            if ($label === null) {
                $label = $countryCode;
            }

            $mappedCountries[$label] = $countryCode;
        }

        // sort
        $sortedCountries = array_keys($mappedCountries);
        asort($sortedCountries);

        foreach ($sortedCountries as $country) {
            $return[] = [
                'value'=>$mappedCountries[$country],
                'label'=>$country
            ];
        }

        return $return;
    }

    /**
     * @return $this
     */
    private function initCountryMap()
    {
        $this->countryMap = [];
        $countries = $this->country->toOptionArray(false);
        foreach ($countries as $country) {
            $this->countryMap[$country['value']] = $country['label'];
        }

        return $this;
    }

    /**
     * @param $countryCode
     *
     * @return |null
     */
    private function getCountryLabelByCode($countryCode)
    {
        if (array_key_exists($countryCode, $this->countryMap)) {
            return $this->countryMap[$countryCode];
        }

        return null;
    }
}

