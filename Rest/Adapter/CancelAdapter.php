<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\RestClient;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class CancelAdapter
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
        \Briqpay\Checkout\Helper\UserAgent $userAgent
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->userAgent = $userAgent;
    }

    /**
     * @param $accessToken
     * @param $purchaseId
     * @param $reason
     *
     * @throws AdapterException
     */
    public function cancel($accessToken, $purchaseId, $reason) : void
    {
        return;
        if (!$purchaseId) {
            throw new AdapterException('Missing purchase id');
        }

        $uri = sprintf(
            '%s/api/partner/payments/%s/cancel',
            $this->endpoint,
            $purchaseId
        );

        $requestBody = \json_encode([
            'reason' => $reason
        ]);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $accessToken),
            'User-Agent' => $this->userAgent->getHeader()
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);
        } catch (\Exception $e) {
            throw new AdapterException($e->getMessage());
        }
    }
}
