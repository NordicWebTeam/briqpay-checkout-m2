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
        \Briqpay\Checkout\Helper\UserAgent $userAgent,
        $clientId = '',
        $clientSecret = ''
    )
    {
        $this->userAgent = $userAgent;
        $this->authHeader = sprintf("Basic %s", base64_encode("$clientId:$clientSecret"));
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
