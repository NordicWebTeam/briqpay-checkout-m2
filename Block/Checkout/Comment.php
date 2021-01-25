<?php declare(strict_types=1);

namespace Briqpay\Checkout\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Comment extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Briqpay\Checkout\Model\Config\Checkout
     */
    private $checkoutConfig;

    /**
     * Comment constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        \Briqpay\Checkout\Model\Config\Checkout $checkoutConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     *
     */
    public function getCommentSubmitUrl()
    {
        $this->checkoutConfig->getCommentSubmitUrl();
    }
}
