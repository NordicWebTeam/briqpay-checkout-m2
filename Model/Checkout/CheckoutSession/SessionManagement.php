<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\CheckoutSession;

use Magento\Checkout\Model\Session;

class SessionManagement
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * SessionManagement constructor.
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->checkoutSession->getBriqpaySessionId();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setSessionId($id)
    {
        return $this->checkoutSession->setBriqpaySessionId($id);
    }

    /**
     * @return mixed
     */
    public function getSessionToken()
    {
        return $this->checkoutSession->getBriqpaySessionToken();
    }

    /**
     * @param $token
     *
     * @return mixed
     */
    public function setSessionToken($token)
    {
        return $this->checkoutSession->setBriqpaySessionToken($token);
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
