<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\CheckoutSession;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\PurchasePaymentMethod;
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
     * @param $token
     *
     * @return mixed
     */
    public function setSnippet($snippet)
    {
        return $this->checkoutSession->setBriqpaySnippet($snippet);
    }

    /**
     * @return mixed
     */
    public function getSnippet()
    {
        return $this->checkoutSession->getBriqpaySnippet();
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

    /**
     *
     */
    public function clear()
    {
        $this->checkoutSession->unsBriqpaySessionId(null);
        $this->checkoutSession->unsBriqpaySessionToken(null);
        $this->checkoutSession->unsBriqpaySnippet(null);
    }

    /**
     * @param $method
     *
     * @return
     */
    public function setBriqpayPaymentMethod($method)
    {
        return $this->checkoutSession->setBriqpayPaymentMethod($method);
    }

    /**
     * @return PurchasePaymentMethod|null
     */
    public function getBriqpayPaymentMethod(): ?PurchasePaymentMethod
    {
        return $this->checkoutSession->getBriqpayPaymentMethod();
    }

    /**
     * @return array
     */
    public function getSessionData()
    {
        return [
            'sessionid' => $this->getSessionId(),
            'token' => $this->getSessionToken(),
            'snippet' => $this->getSnippet()
        ];
    }
}
