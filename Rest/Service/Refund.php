<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Config\ApiConfig;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\OrderRepository;

class Refund
{
    /**
     * @var ApiConfig
     */
    private $config;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Briqpay\Checkout\Rest\Adapter\GetAuthTokenForSession
     */
    private $sessionAuthTokenService;

    /**
     * @var Authentication
     */
    private $authenticationService;

    /**
     * @var OrderLineCollectorsAgreggator
     */
    private $orderLineCollectorsAgreggator;

    /**
     * @var \Briqpay\Checkout\Rest\Request\AuthRequestFactory
     */
    private $authRequestFactory;

    /**
     * @var \Briqpay\Checkout\Rest\Adapter\RefundAdapter
     */
    private $refundAdapter;

    /**
     * OrderDelivery constructor.
     *
     * @param ApiConfig $config
     * @param \Briqpay\Checkout\Rest\Adapter\CapturePayment $orderDelivery
     */
    public function __construct(
        ApiConfig $config,
        \Briqpay\Checkout\Rest\Adapter\RefundAdapter $refundAdapter,
        \Briqpay\Checkout\Rest\Adapter\GetAuthTokenForSession $sessionAuthTokenService,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService,
        OrderRepository $orderRepository,
        OrderLineCollectorsAgreggator $orderLineCollectorsAgreggator,
        \Briqpay\Checkout\Rest\Request\AuthRequestFactory $authRequestFactory
    )
    {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->sessionAuthTokenService = $sessionAuthTokenService;
        $this->authenticationService = $authenticationService;
        $this->orderLineCollectorsAgreggator = $orderLineCollectorsAgreggator;
        $this->authRequestFactory = $authRequestFactory;
        $this->refundAdapter = $refundAdapter;
    }

    /**
     * @param OrderAdapterInterface $order
     * @param $sessionId
     * @param $amount
     *
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     * @throws \Briqpay\Checkout\Rest\Exception\RefundException
     */
    public function refund(OrderAdapterInterface $order, $sessionId, $amount)
    {
        $authRequest = $this->authRequestFactory->create([
            'clientId' => $this->config->getClientId($order->getStoreId()),
            'clientSecret' => $this->config->getClientSecret($order->getStoreId())
        ]);

        $token = $this->sessionAuthTokenService->generateToken($sessionId, $authRequest->getAuthHeader());
        $subjectDto = $this->orderLineCollectorsAgreggator->aggregateItems($order);

        $this->refundAdapter->refund(
            $token,
            $sessionId,
            $amount * 100,
            $subjectDto->getCart()
        );
    }
}
