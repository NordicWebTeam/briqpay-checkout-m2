<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Webservice;

use Briqpay\Checkout\Rest\Webservice\Exception\HttpRequestException;
use Briqpay\Checkout\Rest\Webservice\Exception\HttpResponseException;
use Laminas\Http\Client;
use Laminas\Http\ClientFactory;

class HttpClient
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_PATCH  = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var Client
     */
    private $client;

    /**
     * HttpClient constructor.
     * @param ClientFactory $clientFactory
     */
    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create();
    }

    /**
     * @param string[] $headers
     * @return Client
     */
    public function setHeaders(array $headers)
    {
        return $this->client->setHeaders($headers);
    }

    /**
     * @param string $uri
     * @return Client
     */
    public function setUri($uri)
    {
        return $this->client->setUri($uri);
    }

    /**
     * @param string[] $options
     * @return Client
     */
    public function setOptions(array $options)
    {
        return $this->client->setOptions($options);
    }

    /**
     * @param string $rawBody
     * @return Client
     */
    public function setRawBody($rawBody)
    {
        return $this->client->setRawBody($rawBody);
    }

    /**
     * @param string[] $queryParams
     * @return Client
     */
    public function setParameterGet($queryParams)
    {
        return $this->client->setParameterGet($queryParams);
    }

    /**
     * @param string $method
     * @return string The response body
     * @throws HttpRequestException
     * @throws HttpResponseException
     */
    public function send($method)
    {
        $this->client->setMethod($method);

        try {
            $response = $this->client->send();
        } catch (\Laminas\Http\Exception\RuntimeException $e) {
            throw new HttpRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isSuccess()) {
            throw new HttpResponseException(
                $response->getBody(),
                $response->getStatusCode(),
                null,
                $response->getHeaders()->toString()
            );
        }

        return $response->getBody();
    }
}
