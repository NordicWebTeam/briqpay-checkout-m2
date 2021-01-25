<?php

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Authentification\AdapterException;
use Briqpay\Checkout\Rest\Adapter\Authentification;
use Briqpay\Checkout\Rest\Request\AuthRequestFactory;
use Briqpay\Checkout\Rest\Response\AuthentificationResponse;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Authentication implements AuthentificationInterface
{
    /**
     * @var ApiConfig
     */
    private $config;

    /**
     * @var Authentification
     */
    private $authAdapter;

    /**
     * @var AuthRequestFactory
     */
    private $authRequestFactory;

    /**
     * @var DateTime
     */
    private $datetime;

    /**
     * @var AuthentificationResponse
     */
    private $authentificationResponse;

    /**
     * Authentication constructor.
     *
     * @param ApiConfig $config
     * @param Authentification $authAdapter
     * @param AuthRequestFactory $authRequestFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DateTime $datetime
     */
    public function __construct(
        ApiConfig $config,
        Authentification $authAdapter,
        AuthRequestFactory $authRequestFactory,
        DateTime $datetime
    ) {
        $this->config = $config;
        $this->authAdapter = $authAdapter;
        $this->authRequestFactory = $authRequestFactory;
        $this->datetime = $datetime;
    }

    /**
     * Save API auth response
     *
     * @return void
     */
    private function setAuthResponse(AuthentificationResponse $authentificationResponse) : void
    {
        $this->authentificationResponse = $authentificationResponse;
    }

    /**
     * @return string
     * @throws AuthenticationException
     */
    public function getToken() : string
    {
        if (is_null($this->authentificationResponse)) {
            throw new \Magento\Framework\Exception\AuthenticationException('Authentificaion token is not established. Please authentificate first.');
        }

        return $this->authentificationResponse->getToken();
    }

    /**
     * Get auth token expiry
     *
     * @return string
     * @throws AdapterException
     */
    public function getTokenExpiry() : string
    {
        if (is_null($this->authentificationResponse)) {
            throw new AdapterException('Authentificaion token is not established. Please authentificate first.');
        }

        return $this->authentificationResponse->getTokenExpirationUtc();
    }

    /**
     * Authenticate action
     *
     * @param null $storeId
     *
     * @throws AuthenticationException
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     */
    public function authenticate($storeId = null) : void
    {
        try {
            $authRequest = $this->authRequestFactory->create([
                'clientId' => $this->config->getClientId($storeId),
                'clientSecret' => $this->config->getClientSecret($storeId)
            ]);
            $authResponse = $this->authAdapter->startSession($authRequest);
            $this->setAuthResponse($authResponse);
        } catch (AdapterException $e) {
            $msg = 'API connection could not be established using given credentials (%1).';
            throw new AuthenticationException(__($msg, $e->getMessage()), $e);
        }
    }
}
