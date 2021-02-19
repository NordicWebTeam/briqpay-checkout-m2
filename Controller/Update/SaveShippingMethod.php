<?php

namespace Briqpay\Checkout\Controller\Update;

use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Model\Content\ResponseHandler;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SaveShippingMethod extends \Magento\Checkout\Controller\Action
{
    use ResponseHandler;

    /**
     * @var SessionManagement
     */
    private $sessionManagement;

    /**
     * @var \Briqpay\Checkout\Model\Quote\UpdateCartService
     */
    private $updateCartService;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param SessionManagement $sessionManagement
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Briqpay\Checkout\Model\Quote\UpdateCartService $updateCartService,
        SessionManagement $sessionManagement
    )
    {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );

        $this->sessionManagement = $sessionManagement;
        $this->updateCartService = $updateCartService;
    }

    /**
     * Save shipping method action
     */
    public function execute()
    {
        if (!$this->ajaxRequestAllowed()) {
            return;
        }

        $shippingMethod = $this->getRequest()->getPost('shipping_method');
        $postcode = $this->getRequest()->getPost('postcode');
        if (!$shippingMethod) {
            $this->getResponse()->setBody(json_encode([
                'messages' => 'Please choose a valid shipping method.'
            ]));
            return;
        }

        try {
            $this->updateShippingMethod($shippingMethod, $postcode);

            $sessionId = $this->sessionManagement->getSessionId();
            $sessionToken = $this->sessionManagement->getSessionToken();
            $this->updateCartService->updateByQuote(
                $sessionId,
                $this->sessionManagement->getQuote(),
                $sessionToken
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t update shipping method.')
            );
        }

        $this->handleResponse(['cart', 'coupon', 'messages', 'briqpay', 'newsletter']);
    }

    /**
     * @param $methodCode
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateShippingMethod($methodCode, $postcode = null)
    {
        $quote = $this->getQuote();

        if ($quote->isVirtual()) {
            return;
        }

        $shippingAddress = $quote->getShippingAddress();
        if ($methodCode != $shippingAddress->getShippingMethod() ||
            $shippingAddress->getPostcode() != $postcode ||
            $methodCode == 'nwtunifaun_udc') {
            if ($postcode) {
                $shippingAddress->setPostcode($postcode);
            }

            $this->ignoreAddressValidation();
            $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);
            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     */
    private function ignoreAddressValidation()
    {
        $quote = $this->getQuote();
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Public, since used in plugins
     *
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->sessionManagement->getQuote();
    }
}

