<?php

namespace Briqpay\Checkout\Rest;

use Briqpay\Checkout\Rest\Exception\RestClientErrorException;
use Briqpay\Checkout\Rest\Exception\RestResponseException;
use Briqpay\Checkout\Rest\Webservice\Exception\HttpException;
use Briqpay\Checkout\Rest\Webservice\HttpClient;
use Briqpay\Checkout\Rest\Webservice\HttpClientFactory;

class RestClient
{
    /**
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * RestClient constructor.
     *
     * @param HttpClientFactory $httpClientFactory
     */
    public function __construct(HttpClientFactory $httpClientFactory)
    {
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @param $uri
     * @param $rawBody
     * @param array $headers
     *
     * @return string
     * @throws RestClientErrorException
     * @throws RestResponseException
     * @throws Webservice\Exception\HttpRequestException
     * @throws Webservice\Exception\HttpResponseException
     */
    public function post($uri, $rawBody, array $headers)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders($headers);
        $httpClient->setUri($uri);
        $httpClient->setOptions([
            'trace' => 1,
            'maxredirects' => 0,
            'timeout' => 30,
            'useragent' => 'M2'
        ]);
        $httpClient->setRawBody($rawBody);

        try {
            $response = $httpClient->send(HttpClient::METHOD_POST);
        } catch (HttpException $e) {
            $errorCode = $e->getCode();
            if ($errorCode < 500 && $errorCode > 401) {
                // handle client errors with parseable content
                throw new RestClientErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw new RestResponseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $response;
    }

    /**
     * @param string $uri
     * @param string $rawBody
     * @param string[] $headers
     *
     * @return string
     * @throws RestClientErrorException
     * @throws RestResponseException
     */
    public function put($uri, $rawBody, array $headers)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders($headers);
        $httpClient->setUri($uri);
        $httpClient->setOptions(['trace' => 1, 'maxredirects' => 0, 'timeout' => 30, 'useragent' => 'M2']);
        $httpClient->setRawBody($rawBody);

        try {
            $response = $httpClient->send(HttpClient::METHOD_PUT);
        } catch (HttpException $e) {
            $errorCode = $e->getCode();
            if ($errorCode < 500 && $errorCode >= 400) {
                // handle client errors with parseable content
                throw new RestClientErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw new RestResponseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $response;
    }

    /**
     * @param string $uri
     * @param string $rawBody
     * @param string[] $headers
     *
     * @return string
     * @throws RestClientErrorException
     * @throws RestResponseException
     */
    public function patch($uri, $rawBody, array $headers)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders($headers);
        $httpClient->setUri($uri);
        $httpClient->setOptions(['trace' => 1, 'maxredirects' => 0, 'timeout' => 30, 'useragent' => 'M2']);
        $httpClient->setRawBody($rawBody);

        try {
            $response = $httpClient->send(HttpClient::METHOD_PATCH);
        } catch (HttpException $e) {
            $errorCode = $e->getCode();
            if ($errorCode < 500 && $errorCode >= 400) {
                // handle client errors with parseable content
                throw new RestClientErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw new RestResponseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $response;
    }

    /**
     * @param string $uri
     * @param string[] $queryParams
     * @param string[] $headers
     *
     * @return string
     * @throws RestClientErrorException
     * @throws RestResponseException
     */
    public function get($uri, array $queryParams, array $headers)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders($headers);
        $httpClient->setUri($uri);
        $httpClient->setOptions(['trace' => 1, 'maxredirects' => 0, 'timeout' => 30, 'useragent' => 'M2']);
        $httpClient->setParameterGet($queryParams);

        try {
            $response = $httpClient->send(HttpClient::METHOD_GET);
        } catch (HttpException $e) {
            $errorCode = $e->getCode();
            if ($errorCode < 500 && $errorCode >= 400) {
                // handle client errors with parseable content
                throw new RestClientErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw new RestResponseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $response;
    }

    /**
     * @param string $uri
     * @param string[] $headers
     *
     * @return string
     * @throws RestClientErrorException
     * @throws RestResponseException
     */
    public function delete($uri, array $headers)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders($headers);
        $httpClient->setUri($uri);
        $httpClient->setOptions(['trace' => 1, 'maxredirects' => 0, 'timeout' => 30, 'useragent' => 'M2']);

        try {
            $response = $httpClient->send(HttpClient::METHOD_DELETE);
        } catch (HttpException $e) {
            $errorCode = $e->getCode();
            if ($errorCode < 500 && $errorCode >= 400) {
                // handle client errors with parseable content
                throw new RestClientErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw new RestResponseException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $response;
    }
}
