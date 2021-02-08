<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Authentification;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Adapter\Authentification;
use Briqpay\Checkout\Rest\Response\AuthentificationResponse;
use Briqpay\Checkout\Rest\Service\AuthentificationInterface;
use Briqpay\Checkout\Rest\Request\AuthRequestFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class HttpAuthentication implements AuthentificationInterface
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
     * @var \Briqpay\Checkout\Rest\Response\AuthentificationResponse
     */
    private $authentificationResponse;

    /**
     * Authentication constructor.
     *
     * @param ApiConfig $config
     * @param Authentification $authAdapter
     * @param AuthRequestFactory $authRequestFactory
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
     * @param null $websiteId
     *
     * @throws AuthenticationException
     */
    public function authenticate($websiteId = null) : void
    {
        try {
            $authRequest = $this->authRequestFactory->create([
                'clientId'     => $this->config->getClientId($websiteId),
                'clientSecret' => $this->config->getClientSecret($websiteId)
            ]);

            $authResponse = $this->authAdapter->startSession($authRequest);
            $this->setAuthResponse($authResponse);
        } catch (\Exception $e) {
            $msg = 'API connection could not be established using given credentials (%1).';
            throw new AuthenticationException(__($msg, $e->getMessage()), $e);
        }
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
     * Get auth token
     *
     * @return string
     * @throws AdapterException
     */
    public function getToken() : string
    {
        if (is_null($this->authentificationResponse)) {
            throw new AdapterException('Authentificaion token is not established. Please authentificate first.');
        }

        return $this->authentificationResponse->getToken();
    }
}
