<?php

namespace Briqpay\Checkout\Rest\Request;

class AuthRequest
{
    /**
     * @var string
     */
    private $authHeader;

    /**
     * @var \Briqpay\Checkout\Helper\UserAgent
     */
    private $userAgent;

    /**
     * AuthRequest constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(
        $clientId = '',
        $clientSecret = '',
        \Briqpay\Checkout\Helper\UserAgent $userAgent
    )
    {
        $this->authHeader = sprintf("Basic %s", base64_encode("$clientId:$clientSecret"));
        $this->userAgent = $userAgent;
    }

    /**
     *
     */
    public function getAuthHeaders()
    {
        return [
            'Authorization' => $this->authHeader,
            'User-Agent' => $this->userAgent->getHeader(),
        ];
    }

    /**
     * @return string
     */
    public function getAuthHeader()
    {
        return $this->authHeader;
    }
}
