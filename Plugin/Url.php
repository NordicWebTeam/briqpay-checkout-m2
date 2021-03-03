<?php

namespace Briqpay\Checkout\Plugin;

class Url
{
    /**
     * @var \Briqpay\Checkout\Model\Config\ApiConfig
     */
    private $apiConfig;

    /**
     * @var \Briqpay\Checkout\Model\Config\Checkout
     */
    private $checkoutConfig;

    /**
     * Url constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Model\Config\ApiConfig $apiConfig,
        \Briqpay\Checkout\Model\Config\Checkout $checkoutConfig
    )
    {
        $this->apiConfig = $apiConfig;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetCheckoutUrl($subject, $result)
    {
        return $this->apiConfig->isEnabled()
            ? $this->checkoutConfig->getCheckoutUrl()
            : $result;
    }
}
