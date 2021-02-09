<?php

namespace Briqpay\Checkout\Rest\Request;

class AuthRequest
{
    /**
     * @var string
     */
    private $authHeader;

    /**
     * AuthRequest constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct($clientId = '', $clientSecret = '')
    {
        $this->authHeader = sprintf("Basic %s", base64_encode("$clientId:$clientSecret"));
    }

    /**
     *
     */
    public function getAuthHeaders()
    {
        return [
            'Authorization' => $this->authHeader
        ];
    }
}
