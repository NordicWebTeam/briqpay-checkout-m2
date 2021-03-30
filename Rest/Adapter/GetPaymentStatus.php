<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Adapter;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\AdapterException;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Rest\Response\InitializePaymentResponse;
use Briqpay\Checkout\Rest\RestClient;
use Briqpay\Checkout\Rest\Schema\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class GetPaymentStatus
{
    /**
     * @var ApiConfig
     */
    private $config;

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
     * GetPaymentStatus constructor.
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
        $this->config = $config;
        $this->logger = $logger;
        $this->schemaParser = $schemaParser;
        $this->userAgent = $userAgent;
    }

    /**
     * @param $sessionId
     * @param $accessToken
     *
     * @return GetPaymentStatusResponse
     * @throws AdapterException
     */
    public function getStatus($sessionId, $accessToken) : GetPaymentStatusResponse
    {
        if (!$accessToken) {
            throw new AdapterException('Missing access token');
        }

        $uri = sprintf('%s/checkout/v1/readsession', $this->endpoint);
        $body = json_encode(['sessionid' => $sessionId]);

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $accessToken),
            'User-Agent' => $this->userAgent->getHeader()
        ];

        try {
            $rawResponse = $this->restClient->post($uri, $body, $headers);
            $paymentStatusResponse = $this->schemaParser->parse($rawResponse, GetPaymentStatusResponse::class);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $paymentStatusResponse;
    }
}
