<?php

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;
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
        \Briqpay\Checkout\Rest\Schema\Parser $schemaParser,
        \Briqpay\Checkout\Logger\Logger $logger,
        \Briqpay\Checkout\Helper\UserAgent $userAgent
    ) {
        $this->endpoint = $config->getAuthBackendUrl();
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->schemaParser = $schemaParser;
        $this->userAgent = $userAgent;
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
            'Authorization' => sprintf('Bearer %s', $authToken),
            'User-Agent' => $this->userAgent->getHeader()
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));
        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);
            $paymentStatusResponse = $this->schemaParser->parse($rawResponse, GetPaymentStatusResponse::class);
            $this->validatePaymentState($paymentStatusResponse);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }
    }

    /**
     * @param GetPaymentStatusResponse $paymentStatusResponse
     *
     * @throws UpdateCartException
     */
    private function validatePaymentState(GetPaymentStatusResponse $paymentStatusResponse): void
    {
        if ($paymentStatusResponse->isPurchaseComplete()) {
            throw new UpdateCartException('This payment is completed.');
        }
    }
}
