<?php

namespace Briqpay\Checkout\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Sidebar extends Template
{
    /**
     * Sidebar constructor.
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
