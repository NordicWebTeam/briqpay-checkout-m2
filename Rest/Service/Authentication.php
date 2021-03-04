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
     * Authenticate action
     *
     * @param null $storeId
     *
     * @return string
     * @throws AuthenticationException
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     */
    public function authenticate($storeId = null): string
    {
        try {
            $authRequest = $this->authRequestFactory->create([
                'clientId' => $this->config->getClientId($storeId),
                'clientSecret' => $this->config->getClientSecret($storeId)
            ]);
            $authResponse = $this->authAdapter->startSession($authRequest);

            return $authResponse->getToken();
        } catch (AdapterException $e) {
            $msg = 'API connection could not be established using given credentials (%1).';
            throw new AuthenticationException(__($msg, $e->getMessage()), $e);
        }
    }
}
