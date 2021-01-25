<?php

namespace Briqpay\Checkout\Rest\Request;

class AuthRequest
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * AuthRequest constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct($clientId = '', $clientSecret = '')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        $params = [
            'clientId'      => $this->clientId,
            'clientSecret'  => $this->clientSecret
        ];

        return json_encode($params);
    }
}
