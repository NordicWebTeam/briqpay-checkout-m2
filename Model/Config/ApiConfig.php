<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ApiConfig
{
    private const AUTH_STAGE_BASE_URL               = 'https://playground-api.briqpay.com';
    private const AUTH_PRODUCTION_BASE_URL          = 'https://api.briqpay.com';

    private const XML_PATH_CONNECTION_ENABLE        = 'briqpay/connection/enabled';
    private const XML_PATH_CONNECTION_TEST_MODE     = 'briqpay/connection/test_mode';
    private const XML_PATH_CONNECTION_CLIENT_ID     = 'briqpay/connection/client_id';
    private const XML_PATH_CONNECTION_SHARED_SECRET = 'briqpay/connection/shared_secret';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ApiConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getAuthBackendUrl(): string
    {
        return $this->isTestMode()
            ? self::AUTH_STAGE_BASE_URL
            : self::AUTH_PRODUCTION_BASE_URL;
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CONNECTION_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CONNECTION_TEST_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null $websiteId
     *
     * @return string | null
     */
    public function getClientId($websiteId = null) : ?string
    {
        return $websiteId ?
            $this->scopeConfig->getValue(self::XML_PATH_CONNECTION_CLIENT_ID, ScopeInterface::SCOPE_WEBSITE, $websiteId) :
            $this->scopeConfig->getValue(self::XML_PATH_CONNECTION_CLIENT_ID);
    }

    /**
     * @param null $websiteId
     *
     * @return string | null
     */
    public function getClientSecret($websiteId = null) : ?string
    {
        return $websiteId ?
            $this->scopeConfig->getValue(self::XML_PATH_CONNECTION_SHARED_SECRET, ScopeInterface::SCOPE_STORE, $websiteId) :
            $this->scopeConfig->getValue(self::XML_PATH_CONNECTION_SHARED_SECRET);
    }
}
