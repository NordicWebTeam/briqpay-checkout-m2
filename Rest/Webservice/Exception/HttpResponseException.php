<?php

namespace Briqpay\Checkout\Rest\Webservice\Exception;

class HttpResponseException extends HttpException
{
    /**
     * @var string[]
     */
    private $responseHeaders;

    /**
     * HttpResponseException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param string $responseHeaders
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, $responseHeaders = '')
    {
        $this->responseHeaders = $responseHeaders;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \string[]
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }
}
