<?php declare(strict_types=1);

namespace Briqpay\Checkout\Block\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

class Coupon extends \Magento\Framework\View\Element\Template
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
     * @return string|null
     */
    public function getCouponCode() : ?string
    {
        try {
            return $this->getQuote()->getCouponCode();
        } catch (NoSuchEntityException $e) {
            return null;
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getCouponSubmitUrl()
    {
        return $this->checkoutConfig->getSaveCouponUrl();
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
}
