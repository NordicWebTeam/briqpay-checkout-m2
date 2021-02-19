<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class InitializePayment
 *
 * @package Briqpay\Checkout\Rest\Adapter
 */
class UpdateCart
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
     * @param array $items
     * @param $purchaseId
     *
     * @param $authToken
     *
     * @throws UpdateCartException
     */
    public function updateItems(array $data, $purchaseId, $authToken): void
    {
        if (!$purchaseId) {
            throw new UpdateCartException('Missing purchase id');
        }

        $uri = sprintf(
            '%s/checkout/v1/sessions/update',
            $this->endpoint
        );

        $data['sessionid'] = $purchaseId;

        $requestBody = \json_encode($data);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $authToken)
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }
    }
}
