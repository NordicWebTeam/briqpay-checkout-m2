<?php
namespace Briqpay\Checkout\Controller\Update;

use Briqpay\Checkout\Model\Checkout\BriqpayCheckout;
use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Model\Content\ResponseHandler;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
use Briqpay\Checkout\Rest\Request\InitializePaymentRequestFactory;
use Briqpay\Checkout\Rest\Service\Authentication;
use Exception;
use Magento\Checkout\Controller\Action;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Class Cart
 *
 * @package Briqpay\Checkout\Controller\Update
 */
class Cart extends Action
{
    use ResponseHandler;

    /**
     * @var Authentication
     */
    private $authService;

    /**
     * @var InitializePaymentRequestFactory
     */
    private $initializePaymentRequestFactory;

    /**
     * @var BriqpayCheckout
     */
    private $briqpayCheckout;

    /**
     * @var SessionManagement
     */
    private $sessionManagement;

    /**
     * Cart constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        SessionManagement $sessionManagement,
        BriqpayCheckout $briqpayCheckout
    )
    {
        parent::__construct($context, $customerSession, $customerRepository, $accountManagement);

        $this->briqpayCheckout = $briqpayCheckout;
        $this->sessionManagement = $sessionManagement;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws AuthenticationException
     * @throws UpdateCartException
     */
    public function execute()
    {
        try {
            $this->briqpayCheckout->updateItems(
                $this->sessionManagement->getSessionId(),
                $this->sessionManagement->getQuote(),
                $this->sessionManagement->getSessionToken()
            );

        } catch (Exception $e) {
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
