<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CheckoutSetup
{
    private const PATH_CALLBACK_URI = 'briqpay/callback';
    private const PATH_WEBHOOK_URI = 'briqpay/webhook';

    const CHECKBOX_STATE_CHECKED = 'Checked';
    const CHECKBOX_STATE_UNCHECKED = 'Unchecked';

    private const XML_PATH_CHECKOUT_DEFAULT_CURRENCY = 'currency/options/base';

    private const XML_PATH_CHECKOUT_LANGUAGE = 'briqpay/checkout_config/language';

    private const XML_PATH_CHECKOUT_ALLOWED_COUNTRIES = 'briqpay/checkout_config/allowed_countries';
    private const XML_PATH_CHECKOUT_DEFAULT_COUNTRY = 'general/country/default';

    private const XML_PATH_CHECKOUT_REGISTER_ON_CHECKOUT = 'briqpay/checkout_config/register_on_checkout';
    private const XML_PATH_CHECKOUT_DIFFERENT_DELIVERY_ADDRESS = 'briqpay/checkout_config/different_delivery_address';

    private const XML_PATH_CHECKOUT_URL_INTEGRITY = 'briqpay/checkout_config/integrity_url';
    private const XML_PATH_CHECKOUT_EMAIL_SUBSCRIPTION_CHECKED = 'briqpay/checkout_config/email_newsletter_subscription_checked';
    private const XML_PATH_CHECKOUT_SMS_SUBSCRIPTION_CHECKED = 'briqpay/checkout_config/sms_newsletter_subscription_checked';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CheckoutSetup constructor.
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     *
     */
    public function getCallbackUrl()
    {
        return $this->urlBuilder->getUrl(self::PATH_CALLBACK_URI);
    }

    /**
     *
     */
    public function getCheckoutLanguage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_LANGUAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSmsNewsletterSubscription()
    {
        $flag = $this->scopeConfig->isSetFlag(self::XML_PATH_CHECKOUT_SMS_SUBSCRIPTION_CHECKED, ScopeInterface::SCOPE_STORE);

        return $this->getCheckboxState($flag);
    }

    /**
     * @return bool
     */
    public function getRegisterOnCheckout()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CHECKOUT_REGISTER_ON_CHECKOUT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getEmailNewsletterSubscription()
    {
        $flag = $this->scopeConfig->isSetFlag(self::XML_PATH_CHECKOUT_EMAIL_SUBSCRIPTION_CHECKED, ScopeInterface::SCOPE_STORE);

        return $this->getCheckboxState($flag);
    }

    /**
     * @return string
     */
    public function getDifferentDeliveryAddress()
    {
        $flag = $this->scopeConfig->isSetFlag(self::XML_PATH_CHECKOUT_DIFFERENT_DELIVERY_ADDRESS, ScopeInterface::SCOPE_STORE);

        return $this->getCheckboxState($flag);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->urlBuilder->getUrl($this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_URL_INTEGRITY, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getItegrityConditionsUrl()
    {
        return $this->urlBuilder->getUrl($this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_URL_INTEGRITY, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_DEFAULT_CURRENCY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isBriqpayMaxamount()
    {
        return $this->scopeConfig->getValue('briqpay/checkout_config/maxamount', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isBriqpayCreditscoring()
    {
        return $this->scopeConfig->getValue('briqpay/checkout_config/creditscoring', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return $this->urlBuilder->getUrl(self::PATH_WEBHOOK_URI);
    }

    /**
     * @return mixed
     */
    public function getCreditscoring()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_DEFAULT_CURRENCY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getAllowedCountries()
    {
        $countriesValues = $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_ALLOWED_COUNTRIES, ScopeInterface::SCOPE_STORE);

        return explode(',', $countriesValues);
    }
}
