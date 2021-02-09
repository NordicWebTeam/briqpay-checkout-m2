<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\RestClient;
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
     * @param $sessionId
     * @param $authToken
     *
     * @throws AdapterException
     */
    public function readSession($sessionId, $authToken) : array
    {
        if (!$sessionId) {
            throw new \Exception('Missing session id');
        }

        $uri = sprintf(
            '%s/checkout/v1/readsession',
            $this->endpoint
        );

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $authToken)
        ];

        $data = json_encode([
            'sessionid' => $sessionId
        ]);
        $this->logger->log(LogLevel::INFO, sprintf("%s\n%s", $uri, $sessionId));
        try {
            $rawResponse = $this->restClient->post($uri, $data, $headers);
            $this->logger->log(LogLevel::INFO, $rawResponse);
            return json_decode($rawResponse, true);
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, "[HTTP {$e->getCode()}] {$e->getMessage()}");
            throw AdapterException::create($e);
        }
    }
}
