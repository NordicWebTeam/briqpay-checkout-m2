<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;

class OrderLineCollectorsAgreggator
{
    /**
     * @var OrderItemCollectorInterface []
     */
    private $orderItemsCollectors;

    /**
     * OrderLineCollectorsAgregator constructor.
     *
     * @param array $orderItemsCollectors
     */
    public function __construct(array $orderItemsCollectors)
    {
        $this->orderItemsCollectors = $orderItemsCollectors;
    }

    /**
     * @param $subject
     *
     * @return CreatePaymentSession
     */
    public function aggregateItems($subject)
    {
        $paymentSessionDTO = new CreatePaymentSession();
        foreach ($this->orderItemsCollectors as $collector) {
            $collector->collect($paymentSessionDTO, $subject);
        }

        return $paymentSessionDTO;
    }
}
