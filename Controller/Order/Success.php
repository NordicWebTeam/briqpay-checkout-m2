<?php
namespace Briqpay\Checkout\Controller\Order;

use Briqpay\Checkout\Rest\Exception\PaymentStatusException;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Magento\Checkout\Controller\Action;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Success extends Action
{
    const BLOCK_NAME_SUCCESS = 'briqpay_checkout_success';

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Briqpay\Checkout\Model\Payment\PaymentManager
     */
    private $paymentManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement
     */
    private $checkoutSessionManager;

    /**
     * @var \Briqpay\Checkout\Rest\Service\SessionManagement
     */
    private $sessionManagement;

    /**
     * Index constructor.
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
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Briqpay\Checkout\Model\Payment\PaymentManager $paymentManager,
        \Briqpay\Checkout\Rest\Service\SessionManagement $sessionManagement,
        \Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement $checkoutSessionManager
    )
    {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
        $this->pageFactory = $pageFactory;
        $this->paymentManager = $paymentManager;
        $this->checkoutSessionManager = $checkoutSessionManager;
        $this->sessionManagement = $sessionManagement;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $this->setBriqpayLayoutData($resultPage);

        return $resultPage;
    }

    /**
     * @param Page $page
     */
    private function setBriqpayLayoutData(\Magento\Framework\View\Result\Page $page)
    {
        $layout = $page->getLayout();

        /** @var \Briqpay\Checkout\Block\Checkout\Order\Success $successBlock */
        $successBlock = $layout->getBlock(self::BLOCK_NAME_SUCCESS);
        if (! $successBlock) {
            return;
        }

        try {
            $paymentResponse = $this->getPaymentStatus();
            $successBlock->setPaymentResponse($paymentResponse);
        } catch (PaymentStatusException $e) {
            echo $e->getMessage();
            // TODO: Log excepttion
        }
    }

    /**
     * @return GetPaymentStatusResponse
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     */
    private function getPaymentStatus(): GetPaymentStatusResponse
    {
        return $this->sessionManagement->readSession(
            $this->checkoutSessionManager->getSessionId(),
            $this->checkoutSessionManager->getSessionToken()
        );
    }
}
