<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;
use Magento\Catalog\Model\Product;
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
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        if ($subject instanceof Quote) {
            $quote = $subject;
            $items = $quote->getAllVisibleItems();

            $orderAmount = 0;
            foreach ($items as $item) {
                if ($this->shouldSkipByProductType($item)) {
                    continue;
                }

                $taxRate = $this->getTaxRate($item->getProduct());
                $orderAmount += $item->getRowTotal();
                $paymentSession->addCartItem([
                    'producttype' => 'physical',
                    'reference' => substr($item->getSku(), 0, 64),
                    'name' => substr($item->getName(), 0, 64),
                    'quantity' => (int)1,
                    'quantityunit' => "pc",
                    'unitprice' => 4500,
                    'discount' => $item->getDiscountAmount() * 100,
                    'taxrate' => $taxRate * 100,
                ]);
            }
        }
        $paymentSession->setAmount(4500);
    }

    /**
     * @param Product $product
     *
     * @return float
     */
    private function getTaxRate(Product $product): float
    {
        $taxClassId = $product->getCustomAttribute('tax_class_id');
        if (!$taxClassId) {
            return 0;
        }

        $productRateId = $taxClassId->getValue();
        $taxRate = $this->taxCalculationService->getCalculatedRate(
            $productRateId,
            $quote->getCustomerId() ?: null,
            $quote->getStoreId()
        );

        return $taxRate ?: 0;
    }

    /**
     * @param Item $item
     */
    private function shouldSkipByProductType(Item $item): bool
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
