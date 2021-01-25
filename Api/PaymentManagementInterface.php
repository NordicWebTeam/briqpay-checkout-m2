<?php

namespace Briqpay\Checkout\Api;

interface PaymentManagementInterface
{
    /**
     * @throws \Briqpay\Checkout\Rest\Exception\PaymentStatusException
     * @return \Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse
     */
    public function getPaymentStatus($purchaseId) : \Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
}
