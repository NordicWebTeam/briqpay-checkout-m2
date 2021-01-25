<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Exception\OrderDeliveryException;
use Exception;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\OrderRepository;

class OrderDelivery
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
     * OrderDelivery constructor.
     *
     * @param ApiConfig $config
     * @param \Briqpay\Checkout\Rest\Adapter\OrderDelivery $orderDelivery
     */
    public function __construct(
        ApiConfig $config,
        \Briqpay\Checkout\Rest\Adapter\OrderDelivery $orderDelivery,
        OrderRepository $orderRepository
    ) {
        $this->config = $config;
        $this->orderDeliveryService = $orderDelivery;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderAdapterInterface $order
     * @param $accessToken
     * @return string
     *
     * @throws OrderDeliveryException
     */
    public function orderDelivery(OrderAdapterInterface $order, $accessToken) : string
    {
        $items = $this->getItems($order);
        $purchaseId = $this->getPurchaseId($order->getId());
        $orderReference = $order->getOrderIncrementId();
        $tranId = $this->generateGuid();

        $this->orderDeliveryService->orderDelivery(
            $accessToken,
            $purchaseId,
            $items,
            $orderReference,
            $tranId
        );

        return $tranId;
    }

    /**
     * @param $orderId
     *
     * @return string|null
     */
    private function getPurchaseId($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            return $order->getExtensionAttributes()->getBriqpayPurchaseId();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param OrderAdapterInterface $order
     *
     * @return array
     */
    private function getItems(OrderAdapterInterface $order)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            for ($itemQty = 0; $itemQty < (int) $item->getQtyOrdered(); $itemQty++) {
                $items[] = [
                    'Description' => $item->getName(),
                    'Notes' => $item->getSku(),
                    'Amount' => $item->getPriceInclTax(),
                    'TaxCode' => $item->getTaxPercent(),
                    'TaxAmount' => $item->getTaxAmount(),
                ];
            }
        }

        return $items;
    }

    /**
     * @return string
     */
    private function generateGuid()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
