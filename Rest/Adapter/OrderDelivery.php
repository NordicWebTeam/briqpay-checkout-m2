<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\OrderDeliveryException;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class OrderDelivery
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
     * @var Parser
     */
    private $schemaParser;

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
     * @param $purchaseId
     * @param array $items
     * @param string $orderReference
     * @param string $tranId
     * @param string $trackingCode
     *
     * @throws OrderDeliveryException
     */
    public function orderDelivery($accessToken, $purchaseId, $items = [], $orderReference = '', $tranId = '', $trackingCode = '') : void
    {
        return;
        if (!$purchaseId) {
            throw new OrderDeliveryException('Missing purchase id');
        }

        $uri = sprintf(
            '%s/api/partner/payments/%s/order',
            $this->endpoint,
            $purchaseId
        );

        $requestBody = \json_encode([
            'items' => $items,
            'OrderReference' => $orderReference,
            'TranId' => $tranId,
            'TrackingCode' => $trackingCode
        ]);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $accessToken)
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
