<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;
use Magento\Quote\Model\Quote;

class ShippingCollector implements OrderItemCollectorInterface
{
    /**
     * @inheritDoc
     */
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        return;
        if ($subject instanceof Quote && !$subject->isVirtual()) {
            /**
                'producttype' => 'physical',
                'reference'   => substr($item->getSku(), 0, 64),
                'name'        => substr($item->getName(), 0, 64),
                'quantity'    => (int) $item->getQty(),
                'quantityunit'=> "pc",
                'unitprice'   => $orderAmount,
                'discount'    => $item->getDiscountAmount() * 100,
                'taxrate'     => $taxRate * 100,
             */

            $shipping = $subject->getShippingAddress();
            $paymentSession->addCartItem([
                'producttype' => 'virtual',
                'reference'   => 'Shipping',
                'name'        => 'Shipping',
                'quantity'    => 1,
                'quantityunit'=> "pc",
                'unitprice'   => $shipping->getShippingInclTax() * 100,
                'discount'    => 0,
                'taxrate'     => $shipping->getShippingInclTax() == 0 ? 0 : (($shipping->getShippingTaxAmount() / $shipping->getShippingInclTax()) * 100),
            ]);
        }
    }
}
