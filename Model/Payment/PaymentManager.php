<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Payment;

use Briqpay\Checkout\Api\PaymentManagementInterface;
use Briqpay\Checkout\Rest\Adapter\GetPaymentStatus;
use Briqpay\Checkout\Rest\Exception\PaymentStatusException;
use Briqpay\Checkout\Rest\Service\Authentication;
use Briqpay\Checkout\Rest\Service\SessionManagement;

class PaymentManager implements PaymentManagementInterface
{
    /**
     * @var Authentication
     */
    private $authenticationService;

    /**
     * @var GetPaymentStatus
     */
    private $getPaymentStatusService;

    /**
     * @var SessionManagement
     */
    private $sessionManagement;

    /**
     * PaymentManager constructor.
     *
     * @param Authentication $authenticationService
     * @param GetPaymentStatus $getPaymentStatusService
     */
    public function __construct(
        Authentication $authenticationService,
        SessionManagement $sessionManagement
    ) {
        $this->authenticationService = $authenticationService;
        $this->sessionManagement = $sessionManagement;
    }

    /**
     * @param $purchaseId
     *
     * @return \Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse
     * @throws PaymentStatusException
     */
    public function getPaymentStatus($purchaseId): \Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse
    {
        try {
            $authToken = $this->authenticationService->getToken();

            return $this->getPaymentStatusService->getStatus($purchaseId, $authToken);
        } catch (\Exception $e) {
            throw PaymentStatusException::create($e);
        }
    }
}
