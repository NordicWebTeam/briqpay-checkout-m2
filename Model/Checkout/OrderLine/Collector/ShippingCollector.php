<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\OrderLine\OrderItemCollectorInterface;
use Briqpay\Checkout\Model\Checkout\OrderLine\OrderLineCollectorsAgreggator;
use Magento\Quote\Model\Quote;

class ShippingCollector implements OrderItemCollectorInterface
{
    /**
     * @inheritDoc
     */
    public function collect(OrderLineCollectorsAgreggator $collectorsAgreggator, $subject)
    {
        if ($subject instanceof Quote && !$subject->isVirtual()) {
            $shipping = $subject->getShippingAddress();
            $collectorsAgreggator->addOrderLine([
                'description'   => 'Shipping',
                'amount'        => round($shipping->getShippingInclTax(), 2),
                'taxAmount'     => round($shipping->getShippingTaxAmount(), 2),
                'taxCode'       => $shipping->getShippingInclTax() == 0 ? 0 : ($shipping->getShippingTaxAmount() / $shipping->getShippingInclTax()),
                'notes'         => 'notes'
            ]);
        }
    }
}
