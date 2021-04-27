<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Exception\InitializePaymentException;
use Briqpay\Checkout\Rest\Request\InitializePaymentRequest;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class InitializePayment
 *
 * @package Briqpay\Checkout\Rest\Adapter
 */
class InitializePayment
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
     * @var \Briqpay\Checkout\Helper\UserAgent
     */
    private $userAgent;

    /**
     * AuthAdapter constructor.
     *
     * @param ApiConfig $config
     * @param RestClient $restClient
     * @param Parser $schemaParser
     * @param \Briqpay\Checkout\Logger\Logger $logger
     * @param \Briqpay\Checkout\Helper\UserAgent $userAgent
     */
    public function __construct(
        ApiConfig $config,
        RestClient $restClient,
        Parser $schemaParser,
        \Briqpay\Checkout\Logger\Logger $logger,
        \Briqpay\Checkout\Helper\UserAgent $userAgent
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->schemaParser = $schemaParser;
        $this->logger = $logger;
        $this->userAgent = $userAgent;
    }

    /**
     * @param \Briqpay\Checkout\Rest\Request\InitializePaymentRequest $request
     *
     * @param $accessToken
     *
     * @return mixed
     * @throws InitializePaymentException
     */
    public function initialize(InitializePaymentRequest $request, $accessToken) : InitializePaymentResponse
    {
        if (!$accessToken) {
            throw new InitializePaymentException('Missing access token');
        }

        $uri = sprintf('%s/checkout/v1/sessions', $this->endpoint);
        $requestBody = $request->getRequestBody();

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $accessToken),
            'User-Agent' => $this->userAgent->getHeader()
        ];

        $this->logger->log(LogLevel::INFO, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::INFO, $rawResponse);

            /** @var InitializePaymentResponse $initPaymentResponse */
            $initPaymentResponse = $this->schemaParser->parse($rawResponse, InitializePaymentResponse::class);
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, "[HTTP {$e->getCode()}] {$e->getMessage()}");
            throw AdapterException::create($e);
        }

        return $initPaymentResponse;
    }
}
