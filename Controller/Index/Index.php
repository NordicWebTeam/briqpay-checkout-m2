<?php

namespace Briqpay\Checkout\Controller\Index;

use Briqpay\Checkout\Block\Checkout\CheckoutWidget;
use Briqpay\Checkout\Model\Checkout\BriqpayCheckout;
use Briqpay\Checkout\Model\Checkout\CheckoutException;
use Magento\Checkout\Controller\Action;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var BriqpayCheckout
     */
    private $checkout;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param BriqpayCheckout $checkout
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        BriqpayCheckout $checkout
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );

        $this->checkout = $checkout;
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        try {
            $paymentData = $this->checkout->initCheckout();
        } catch (CheckoutException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            //$this->_redirect('checkout/cart');
            return;
        }

        return $this->getCheckoutLayout(
            $paymentData->getSnippet(),
            $paymentData->getSessionId()
        );
    }

    /**
     * @param $jwtToken
     * @param $purchaseId
     *
     * @return \Magento\Framework\View\Result\Page
     */
    private function getCheckoutLayout($htmlSnippet, $sessionId)
    {
        $checkoutLayout = $this->createLayoutPage();

        /** @var $briqpayCheckoutBlock CheckoutWidget */
        $briqpayCheckoutBlock = $checkoutLayout->getLayout()->getBlock('checkout.widget');
        $briqpayCheckoutBlock
            ->setHtmlSnippet($htmlSnippet)
            ->setSessionId($sessionId);

        return $checkoutLayout;
    }

    /**
     * Plugin extension point for extending the layout
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function createLayoutPage()
    {
        return $this->pageFactory->create();
    }
}
