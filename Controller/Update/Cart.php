<?php
namespace Briqpay\Checkout\Controller\Update;

use Briqpay\Checkout\Model\Content\ResponseHandler;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Cart
 *
 * @package Briqpay\Checkout\Controller\Update
 */
class Cart extends \Magento\Checkout\Controller\Action
{
    use ResponseHandler;

    /**
     * @var \Briqpay\Checkout\Rest\Service\Authentication
     */
    private $authService;

    /**
     * @var \Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory
     */
    private $initializePaymentRequestFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Briqpay\Checkout\Model\Checkout\BriqpayCheckout
     */
    private $briqpayCheckout;

    /**
     * Cart constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Session $checkoutSession,
        \Briqpay\Checkout\Model\Checkout\BriqpayCheckout $briqpayCheckout
    ) {
        parent::__construct($context, $customerSession, $customerRepository, $accountManagement);

        $this->checkoutSession = $checkoutSession;
        $this->briqpayCheckout = $briqpayCheckout;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Briqpay\Checkout\Rest\Exception\UpdateCartException
     */
    public function execute()
    {
        $checkoutSession = $this->checkoutSession;
        try {
            $this->briqpayCheckout->updateItems($checkoutSession->getBriqpayPurchaseId(), $checkoutSession->getQuote());
        } catch (\Exception $e) {
            $this->getResponse()->setBody(json_encode([
                'messages' => __('Can not update cart.')
            ]));
            return;
        }

        $updateBlocks = [
            'cart',
            'coupon',
            'shipping',
            'messages',
            'briqpay'
        ];

        $this->handleResponse($updateBlocks, true);
    }
}
