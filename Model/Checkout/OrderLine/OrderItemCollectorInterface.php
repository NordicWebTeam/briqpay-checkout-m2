<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\OrderLine;

interface OrderItemCollectorInterface
{
    /**
     * @param OrderLineCollectorsAgreggator $sourcce
     * @param $subject
     *
     * @return mixed
     */
    public function collect(OrderLineCollectorsAgreggator $sourcce, $subject);
}
