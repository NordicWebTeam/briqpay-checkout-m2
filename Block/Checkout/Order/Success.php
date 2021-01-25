<?php

namespace Briqpay\Checkout\Block\Checkout\Order;

use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;

/**
 * Class Success
 *
 * @package Briqpay\Checkout\Block
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var GetPaymentStatusResponse
     */
    protected $paymentResponse;

    /**
     * Success constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\OrderRepository $orderRepository,


        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return mixed
     */
    public function getRealOrderId()
    {
        return $this->_checkoutSession->getLastOrderId();
    }

    public function setPaymentStatus(GetPaymentStatusResponse $paymentResponse)
    {

    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderById($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderItems($orderId)
    {
        return $this->getOrderById($orderId)->getAllVisibleItems();
    }

    /**
     * @return GetPaymentStatusResponse
     */
    public function getPaymentResponse(): GetPaymentStatusResponse
    {
        return $this->paymentResponse;
    }

    /**
     * @param GetPaymentStatusResponse $paymentResponse
     */
    public function setPaymentResponse(GetPaymentStatusResponse $paymentResponse): void
    {
        $this->paymentResponse = $paymentResponse;
    }
}

