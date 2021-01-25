<?php

namespace Briqpay\Checkout\Controller\Update;

use Briqpay\Checkout\Model\Content\ResponseHandler;
use Magento\Checkout\Controller\Action;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SaveCoupon extends Action
{
    use ResponseHandler;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var
     */
    private $quote;

    /**
     * @var \Briqpay\Checkout\Model\Service\QuoteManagement
     */
    private $briqpayQuoteManagement;

    /**
     * @var \Briqpay\Checkout\Rest\Service\UpdateCart
     */
    private $updateCartService;

    /**
     * SaveCoupon constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param Session $checkoutSession
     * @param \Briqpay\Checkout\Model\Quote\UpdateCartService $updateCart
     * @param $
     * @param \Briqpay\Checkout\Model\Service\QuoteManagement $briqpayQuoteManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Session $checkoutSession,
        \Briqpay\Checkout\Model\Quote\UpdateCartService $updateCartService,
        \Briqpay\Checkout\Model\Service\QuoteManagement $briqpayQuoteManagement
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
        $this->checkoutSession = $checkoutSession;
        $this->briqpayQuoteManagement = $briqpayQuoteManagement;
        $this->updateCartService = $updateCartService;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (! $this->ajaxRequestAllowed()) {
            return;
        }

        $quote = $this->getQuote();

        $couponCode    = (string) $this->getRequest()->getParam('coupon_code');
        $oldCouponCode = $quote->getCouponCode();
        $remove        = (int)$this->getRequest()->getParam('remove') > 0;

        if ($remove) {
            $couponCode    = '';
        } elseif ($couponCode) {
            $codeLength = strlen($couponCode);
            if ($codeLength > 255) {
                $couponCode = '';
            }
        }

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->messageManager->addError(__('Coupon code is not valid (or missing)'));
            $this->handleResponse('coupon', false);
            return;
        }

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode($couponCode)->collectTotals()->save();

            if ($couponCode) {
                if ($couponCode == $quote->getCouponCode()) {
                    $this->messageManager->addSuccess(__('Coupon code "%1" was applied.', $couponCode));
                    $this->updateCartService->updateByQuote($this->checkoutSession->getQuote(), $this->checkoutSession->getBriqpayPurchaseId());
                } else {
                    $this->messageManager->addError(__('Coupon code "%1" is not valid.', $couponCode));
                }
            } else {
                $this->messageManager->addSuccess(__('Coupon code was canceled.', $couponCode));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t apply your coupon.')
            );
        }

        $this->handleResponse(['cart','coupon','messages','shipping','briqpay']);
    }

    /**
     * Quote object getter
     *
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            return $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}
