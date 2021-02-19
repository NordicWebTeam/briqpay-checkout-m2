<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;

class SessionManagement
{
    /**
     * @var AuthenticationFactory
     */
    private $initializePaymentRequestFactory;

    /**
     * @var \Briqpay\Checkout\Rest\Adapter\ReadSession
     */
    private $sessionManagementService;

    /**
     * SessionManagement constructor.
     *
     * @param AuthenticationFactory $initializePaymentRequestFactory
     * @param \Briqpay\Checkout\Rest\Adapter\ReadSession $sessionManagementService
     */
    public function __construct(
        \Briqpay\Checkout\Rest\Service\AuthenticationFactory $initializePaymentRequestFactory,
        \Briqpay\Checkout\Rest\Adapter\ReadSession $sessionManagementService
    )
    {
        $this->initializePaymentRequestFactory = $initializePaymentRequestFactory;
        $this->sessionManagementService = $sessionManagementService;
    }

    /**
     * @param $sessionId
     * @param $authToken
     *
     * @return GetPaymentStatusResponse
     * @throws \Briqpay\Checkout\Rest\Exception\AdapterException
     */
    public function readSession($sessionId, $authToken): GetPaymentStatusResponse
    {
        return $this->sessionManagementService->readSession($sessionId, $authToken);
    }
}
