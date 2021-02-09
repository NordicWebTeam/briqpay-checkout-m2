<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Config;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class UrlBuilder
{
    private const PATH_CALLBACK_URI = 'briqpay/callback';

    private const PATH_REDIRECT_URL = 'briqpay/callback';

    private const XML_PATH_CHECKOUT_URL_TERMS = 'briqpay/checkout_config/terms_url';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * UrlBuilder constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getTermsUrl()
    {
        return $this->urlBuilder->getUrl(
            $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_URL_TERMS, ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        if ($proxyBaseUrl = $this->getDevProxyBaseUrl()) {
            return $proxyBaseUrl . self::PATH_CALLBACK_URI;
        }

        return $this->urlBuilder->getUrl(self::PATH_CALLBACK_URI);
    }

    /**
     *
     */
    public function getRedirectUrl()
    {
        return $this->urlBuilder->getUrl(self::PATH_REDIRECT_URL);
    }

    /**
     * @return string|null
     */
    private function getDevProxyBaseUrl()
    {
        return $this->getStoreConfig('dev/briqpay/webhook_domain');
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     */
    private function getStoreConfig($path, $store = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
