<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Request\AuthRequest;
use Briqpay\Checkout\Rest\Response\AuthentificationResponse;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Authentification
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
     * AuthAdapter constructor.
     *
     * @param ApiConfig $config
     * @param RestClient $restClient
     * @param Parser $schemaParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiConfig $config,
        RestClient $restClient,
        Parser $schemaParser,
        \Briqpay\Checkout\Logger\Logger $logger
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->config = $config;
        $this->schemaParser = $schemaParser;
    }

    /**
     * @param AuthRequest $request
     *
     * @return AuthentificationResponse
     */
    public function startSession(AuthRequest $request) : AuthentificationResponse
    {
        $uri = sprintf('%s/auth', $this->endpoint);
        $this->logger->log(
            LogLevel::INFO,
            sprintf("%s", $uri)
        );

        try {
            $rawResponse = $this->restClient->get($uri, [], $request->getAuthHeaders());
            $this->logger->log(LogLevel::INFO, "Auth Response: $rawResponse");
            $response = $this->schemaParser->parse($rawResponse, AuthentificationResponse::class);
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, "Exception response : {$e->getMessage()}");
            throw AdapterException::create($e);
        }

        return $response;
    }
}
