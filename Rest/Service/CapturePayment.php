<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\OrderDeliveryException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\OrderRepository;

class CapturePayment
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
     * @var \Briqpay\Checkout\Rest\Adapter\CapturePayment
     */
    private $capturePaymentAdapter;

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
     * OrderDelivery constructor.
     *
     * @param ApiConfig $config
     * @param \Briqpay\Checkout\Rest\Adapter\CapturePayment $orderDelivery
     */
    public function __construct(
        ApiConfig $config,
        \Briqpay\Checkout\Rest\Adapter\CapturePayment $capturePaymentAdapter,
        \Briqpay\Checkout\Rest\Adapter\GetAuthTokenForSession $sessionAuthTokenService,
        \Briqpay\Checkout\Rest\Service\Authentication $authenticationService,
        OrderRepository $orderRepository,
        OrderLineCollectorsAgreggator $orderLineCollectorsAgreggator,
        \Briqpay\Checkout\Rest\Request\AuthRequestFactory $authRequestFactory
    )
    {
        $this->config = $config;
        $this->capturePaymentAdapter = $capturePaymentAdapter;
        $this->orderRepository = $orderRepository;
        $this->sessionAuthTokenService = $sessionAuthTokenService;
        $this->authenticationService = $authenticationService;
        $this->orderLineCollectorsAgreggator = $orderLineCollectorsAgreggator;
        $this->authRequestFactory = $authRequestFactory;
    }

    /**
     * @param OrderAdapterInterface $order
     * @param $sessionId
     * @param $amount
     *
     * @return string
     * @throws OrderDeliveryException
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function capture(OrderAdapterInterface $order, $sessionId, $amount)
    {
        $authRequest = $this->authRequestFactory->create([
            'clientId' => $this->config->getClientId($order->getStoreId()),
            'clientSecret' => $this->config->getClientSecret($order->getStoreId())
        ]);

        $token = $this->sessionAuthTokenService->generateToken($sessionId, $authRequest->getAuthHeader());
        $subjectDto = $this->orderLineCollectorsAgreggator->aggregateItems($order);

        $this->capturePaymentAdapter->capture(
            $token,
            $sessionId,
            (int)$amount * 100,
            $subjectDto->getCart()
        );
    }

    /**
     * @return string
     */
    private function generateGuid()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
