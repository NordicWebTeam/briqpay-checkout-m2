<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\OrderDeliveryException;
use Briqpay\Checkout\Rest\RestClient;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Magento\Framework\Serialize\Serializer\Json;

class CapturePayment
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Briqpay\Checkout\Helper\UserAgent
     */
    private $userAgent;

    /**
     * @var Json
     */
    private $json;

    /**
     * AuthAdapter constructor.
     *
     * @param ApiConfig $config
     * @param RestClient $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiConfig $config,
        RestClient $restClient,
        \Briqpay\Checkout\Logger\Logger $logger,
        \Briqpay\Checkout\Helper\UserAgent $userAgent,
        Json $json
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->userAgent = $userAgent;
        $this->json = $json;
    }

    /**
     * @param $purchaseId
     * @param array $items
     * @param string $orderReference
     * @param string $tranId
     * @param string $trackingCode
     * @return string Capture ID from response
     *
     * @throws OrderDeliveryException
     */
    public function capture($authToken, $sessionId, $amount, $cart = []): string
    {
        $uri = "{$this->endpoint}/order-management/v1/capture-order";
        $requestBody = \json_encode([
            'sessionid' => $sessionId,
            'amount' => $amount,
            'cart' => $cart
        ]);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authToken",
            'User-Agent' => $this->userAgent->getHeader()
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);
            $decoded = $this->json->unserialize($rawResponse);
            return $decoded['captureid'] ?? '';
        } catch (\Exception $e) {
            throw new OrderDeliveryException($e->getMessage());
        }
    }
}
