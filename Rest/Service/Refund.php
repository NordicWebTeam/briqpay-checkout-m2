<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Adapter\RefundAdapter;
use Briqpay\Checkout\Rest\Exception\RefundException;
use Magento\Sales\Model\OrderRepository;

class Refund
{
    /**
     * @var ApiConfig
     */
    private $config;

    /**
     * @var \Briqpay\Checkout\Rest\Adapter\OrderDelivery
     */
    private $orderDeliveryService;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var RefundAdapter
     */
    private $refundAdapter;

    /**
     * OrderDelivery constructor.
     *
     * @param ApiConfig $config
     * @param \Briqpay\Checkout\Rest\Adapter\OrderDelivery $orderDelivery
     */
    public function __construct(
        ApiConfig $config,
        RefundAdapter $refundAdapter,
        OrderRepository $orderRepository
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->refundAdapter = $refundAdapter;
    }

    /**
     * @param $accessToken
     *
     * @param $purchaseId
     * @param $orderReference
     * @param $transactionId
     * @param $amount
     *
     * @throws RefundException
     */
    public function refund($accessToken, $purchaseId, $orderReference, $transactionId, $amount) : void
    {
        if (! $purchaseId) {
            throw new RefundException('Puchase id not found for target order.');
        }

        $this->refundAdapter->refund($accessToken, $purchaseId, $orderReference, $transactionId, $amount);
    }
}
