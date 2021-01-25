<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\OrderLine\OrderItemCollectorInterface;
use Briqpay\Checkout\Model\Checkout\OrderLine\OrderLineCollectorsAgreggator;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class ItemsCollector implements OrderItemCollectorInterface
{
    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * ItemsCollector constructor.
     */
    public function __construct(\Magento\Tax\Api\TaxCalculationInterface $taxCalculationService)
    {
        $this->taxCalculationService = $taxCalculationService;
    }

    /**
     * @inheritDoc
     */
    public function collect(OrderLineCollectorsAgreggator $orderLineAggregator, $subject)
    {
        if ($subject instanceof Quote) {
            $quote = $subject;
            $items = $quote->getAllVisibleItems();

            foreach ($items as $item) {
                if ($this->shouldSkipByProductType($item)) {
                    continue;
                }

                $itemAmount = round($item->getPrice() * $item->getQty() - $item->getDiscountAmount(), 2);
                $taxClassId = $item->getProduct()->getCustomAttribute('tax_class_id');
                $productRateId = $taxClassId->getValue();
                $totalTaxAmount = $this->taxCalculationService->getCalculatedRate(
                    $productRateId,
                    $quote->getCustomerId() ?: null,
                    $quote->getStoreId()
                );

                $orderLineAggregator->addOrderLine([
                    'description' => substr($item->getName(),0, 64),
                    'amount'      => $itemAmount + $item->getTaxAmount(),
                    'taxAmount'   => $item->getTaxAmount(),
                    'taxCode'     => ($item->getTaxPercent() > 0) ? $item->getTaxPercent() : ($item->getBaseTaxAmount() / $item->getBaseRowTotal() * 100),
                    'notes'       => ''
                ]);
            }
        }
    }

    /**
     * @param Item $item
     */
    private function shouldSkipByProductType(Item $item) : bool
    {
        // Skip if bundle product with a dynamic price type
        if (\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE == $item->getProductType()
            && \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC == $item->getProduct()->getPriceType()
        ) {
            return true;
        }

        return false;
    }
}
