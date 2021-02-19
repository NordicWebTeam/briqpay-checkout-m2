<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\OrderDeliveryException;
use Briqpay\Checkout\Rest\Exception\RefundException;
use Briqpay\Checkout\Rest\RestClient;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RefundAdapter
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
     * AuthAdapter constructor.
     *
     * @param ApiConfig $config
     * @param RestClient $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiConfig $config,
        RestClient $restClient,
        \Briqpay\Checkout\Logger\Logger $logger
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * @param $accessToken
     * @param $purchaseId
     * @param $amount
     *
     * @throws RefundException
     */
    public function refund($authToken, $sessionId, $amount, $cart): void
    {
        $uri = sprintf(
            '%s/order-management/v1/refund-order',
            $this->endpoint
        );

        $refundData = [];
        $refundData['sessionid'] = $sessionId;
        $refundData['amount'] = $amount;
        $refundData['cart'] = $cart;

        $requestBody = \json_encode($refundData);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authToken"
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);
        } catch (\Exception $e) {
            throw new OrderDeliveryException($e->getMessage());
        }
    }
}
