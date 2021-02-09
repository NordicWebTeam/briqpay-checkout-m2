<?php

namespace Briqpay\Checkout\Block\Checkout;

use Magento\Framework\View\Element\Template;

/**
 * @method setSessionId(string $id) : self
 * @method setHtmlSnippet(string $html) : self
 */
class CheckoutWidget extends Template
{
    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutSetupConfig;

    /**
     * Sidebar constructor.
     *
     * @param Template\Context $context
     * @param \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSetupConfig = $checkoutSetupConfig;
    }

    /**
     * @return string
     */
    public function getCheckoutCallbackUrl()
    {
        return $this->checkoutSetupConfig->getCallbackUrl();
    }

    /**
     * @return string
     */
    public function getCheckoutSnippet()
    {
        return $this->getData('html_snippet');
    }
}
