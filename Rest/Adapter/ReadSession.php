<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class InitializePayment
 *
 * @package Briqpay\Checkout\Rest\Adapter
 */
class ReadSession
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
     * @var ApiConfig
     */
    private $config;

    /**
     * @var Parser
     */
    private $schemaParser;

    /**
     * @var \Briqpay\Checkout\Helper\UserAgent
     */
    private $userAgent;

    /**
     * ReadSession constructor.
     *
     * @param ApiConfig $config
     * @param RestClient $restClient
     * @param Parser $schemaParser
     * @param \Briqpay\Checkout\Logger\Logger $logger
     */
    public function __construct(
        ApiConfig $config,
        RestClient $restClient,
        Parser $schemaParser,
        \Briqpay\Checkout\Logger\Logger $logger,
        \Briqpay\Checkout\Helper\UserAgent $userAgent
    )
    {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->config = $config;
        $this->schemaParser = $schemaParser;
        $this->userAgent = $userAgent;
    }

    /**
     * @param $sessionId
     * @param $authToken
     *
     * @throws AdapterException
     */
    public function readSession($sessionId, $authToken): GetPaymentStatusResponse
    {
        if (!$sessionId) {
            throw new AdapterException('Missing session id');
        }

        $uri = sprintf(
            '%s/checkout/v1/readsession',
            $this->endpoint
        );

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $authToken),
            'User-Agent' => $this->userAgent->getHeader()
        ];

        $data = json_encode([
            'sessionid' => $sessionId
        ]);
        $this->logger->log(LogLevel::INFO, sprintf("%s\n%s", $uri, $sessionId));
        try {
            $rawResponse = $this->restClient->post($uri, $data, $headers);
            $this->logger->log(LogLevel::INFO, $rawResponse);

            return $this->schemaParser->parse($rawResponse, GetPaymentStatusResponse::class);
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, "[HTTP {$e->getCode()}] {$e->getMessage()}");
            throw AdapterException::create($e);
        }
    }
}
