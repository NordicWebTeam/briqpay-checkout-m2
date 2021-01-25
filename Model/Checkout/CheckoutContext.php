<?php

namespace Briqpay\Checkout\Model\Checkout;

use Briqpay\Checkout\Model\Quote\QuoteManagement;
use Briqpay\Checkout\Rest\Service\AuthenticationFactory;
use Briqpay\Checkout\Rest\Service\InitializePaymentFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Result\PageFactory;

class CheckoutContext
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var AuthenticationFactory
     */
    private $authenticationFactory;

    /**
     * @var InitializePaymentFactory
     */
    private $initializePaymentFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * CheckoutContext constructor.
     *
     * @param PageFactory $pageFactory
     * @param AuthenticationFactory $authenticationFactory
     * @param InitializePaymentFactory $initializePaymentFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        AuthenticationFactory $authenticationFactory,
        InitializePaymentFactory $initializePaymentFactory,
        Session $checkoutSession,
        QuoteManagement $quoteManagement
    ) {
        $this->pageFactory = $pageFactory;
        $this->authenticationFactory = $authenticationFactory;
        $this->initializePaymentFactory = $initializePaymentFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
    }

    /**
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }

    /**
     * @return AuthenticationFactory
     */
    public function getAuthenticationFactory(): AuthenticationFactory
    {
        return $this->authenticationFactory;
    }

    /**
     * @return InitializePaymentFactory
     */
    public function getInitializePaymentFactory(): InitializePaymentFactory
    {
        return $this->initializePaymentFactory;
    }

    /**
     * @return Session
     */
    public function getCheckoutSession(): Session
    {
        return $this->checkoutSession;
    }

    /**
     * @return QuoteManagement
     */
    public function getQuoteManagement(): QuoteManagement
    {
        return $this->quoteManagement;
    }
}
