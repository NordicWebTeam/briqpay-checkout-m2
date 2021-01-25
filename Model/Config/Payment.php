<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Payment
 *
 * @package Briqpay\Checkout\Model\Config
 */
class Payment extends AbstractHelper
{
    const XML_PATH_CARD_AUTOCAPTURE = 'briqpay/checkout_config/autocapture';

    /**
     * Payment constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCheckoutUrl($params = [])
    {
        return $this->_getUrl(self::PATH_CHECKOUT_URI, $params);
    }

    /**
     * @return bool
     */
    public function isAutoCapture()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CARD_AUTOCAPTURE);
    }

    /**
     * @return bool
     */
    public function isRegisterOnCheckout()
    {
        return true;
    }
}
