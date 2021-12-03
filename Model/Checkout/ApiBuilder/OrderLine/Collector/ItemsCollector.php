<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class ItemsCollector implements OrderItemCollectorInterface
{
    /**
     * Checkout item types
     */
    const ITEM_TYPE_PHYSICAL = 'physical';
    const ITEM_TYPE_VIRTUAL = 'digital';

    /**
     * @inheritDoc
     */
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        if ($subject instanceof Quote) {
            $quote = $subject;
            foreach ($this->generateItems($quote->getAllItems()) as $item) {
                $paymentSession->addCartItem($item);
            }
            $paymentSession->addAmount(
                $this->toApiFloat(
                    $quote->getBaseGrandTotal() - $quote->getShippingAddress()->getShippingInclTax()
                )
            );
        }

        if ($subject instanceof \Magento\Payment\Gateway\Data\OrderAdapterInterface) {
            $orderAmount = 0;
            foreach ($this->generateItems($subject->getItems()) as $item) {
                $orderAmount += $item['unitprice'] * $item['quantity'];
                $paymentSession->addCartItem($item);
            }
            $paymentSession->setAmount($orderAmount);
        }
    }

    /**
     * @param $allItems
     *
     * @return array
     */
    private function generateItems($allItems)
    {
        $items = [];
        foreach ($allItems as $item) {
            $item = $this->getItem($item);

            $parentItem = $item->getParentItem()
                ?: ($item->getParentItemId() ? $object->getItemById($item->getParentItemId()) : null);

            if ($this->shouldSkip($parentItem, $item)) {
                continue;
            }

            $items[] = $this->processItem($item);
        }

        return $items;
    }

    /**
     * @param QuoteItem|InvoiceItem|CreditMemoItem $item
     *
     * @return QuoteItem|OrderItem
     */
    private function getItem($item)
    {
        if ($item instanceof InvoiceItem || $item instanceof CreditMemoItem) {
            $orderItem = $item->getOrderItem();
            $orderItem->setCurrentInvoiceRefundItemQty($item->getQty());
            return $orderItem;
        }

        return $item;
    }

    /**
     * @param $parentItem
     * @param $item
     * @return bool
     */
    private function shouldSkip($parentItem, $item)
    {
        // Skip if bundle product with a dynamic price type
        if (Type::TYPE_BUNDLE == $item->getProductType()
            && Price::PRICE_TYPE_DYNAMIC == $item->getProduct()->getPriceType()
        ) {
            return true;
        }

        if (!$parentItem) {
            return false;
        }

        // Skip if child product of a non bundle parent
        if (Type::TYPE_BUNDLE != $parentItem->getProductType()) {
            return true;
        }

        // Skip if non bundle product or if bundled product with a fixed price type
        if (Type::TYPE_BUNDLE != $parentItem->getProductType()
            || Price::PRICE_TYPE_FIXED == $parentItem->getProduct()->getPriceType()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return array
     */
    private function processItem($item)
    {
        return [
            'producttype' => $item->getIsVirtual() ? self::ITEM_TYPE_VIRTUAL : self::ITEM_TYPE_PHYSICAL,
            'reference' => substr($item->getSku(), 0, 64),
            'name' => $item->getName(),
            'quantity' => ceil($this->getItemQty($item)),
            'quantityunit' => 'pc',
            'unitprice' => $this->toApiFloat($item->getBasePrice()),
            'taxrate' => $this->toApiFloat($item->getTaxPercent()),
            'discount' => $this->toApiFloat($item->getDiscountPercent())
        ];
    }

    /**
     * @param QuoteItem|InvoiceItem|CreditMemoItem $item
     *
     * @return int
     */
    private function getItemQty($item)
    {
        $methods = ['getQty', 'getCurrentInvoiceRefundItemQty', 'getQtyOrdered'];
        foreach ($methods as $method) {
            if ($item->$method() !== null) {
                return $item->$method();
            }
        }

        return 0;
    }

    /**
     *
     */
    private function toApiFloat($float)
    {
        return (int)round($float * 100);
    }
}
