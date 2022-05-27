<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;
use Klarna\Core\Api\BuilderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;

class ShippingCollector implements OrderItemCollectorInterface
{
    /**
     * @var Calculation
     */
    private $taxCalculator;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ShippingCollector constructor.
     *
     * @param Calculation $taxCalculator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Calculation $taxCalculator,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->taxCalculator = $taxCalculator;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function collect(CreatePaymentSession $paymentSession, $subject)
    {
        if ($subject instanceof CartInterface) {
            $store = $subject->getStore();
            $totals = $subject->getTotals();
            if (isset($totals['shipping'])) {
                /** @var Address $total */
                $total = $totals['shipping'];
                $address = $subject->getShippingAddress();
                $discountAmount = $address->getBaseShippingDiscountAmount();

                $taxRate = $this->calculateShippingTax($subject, $store);
                $unitPrice = $address->getBaseShippingInclTax();
                $amount = $this->toApiFloat($address->getBaseShippingInclTax() - $discountAmount);

                $paymentSession->addCartItem([
                    'producttype' => 'virtual',
                    'reference' => $total->getCode(),
                    'name' => $total->getName() ?: $total->getCode(),
                    'quantity' => 1,
                    'quantityunit' => 'pc',
                    'unitprice' => $amount,
                    'taxrate' => $this->toApiFloat($taxRate),
                    'discount' => $this->toApiFloat($discountAmount)
                ]);

                $paymentSession->addAmount($amount);
            }
        }

        if (($subject instanceof Order) && !$subject->getIsVirtual()) {
            $unitPrice = $subject->getBaseShippingInclTax();
            $taxRate = $this->calculateShippingTax($subject, $subject->getStore());
            //$taxAmount = $subject->getShippingTaxAmount() + $object->getShippingHiddenTaxAmount();

            $paymentSession->addCartItem([
                'type' => 'virtual',
                'reference' => $subject->getShippingMethod(),
                'name' => __('Shipping & Handling')->getText(),
                'quantity' => 1,
                'quantityunit' => 'pc',
                'unitprice' => $this->toApiFloat($unitPrice),
                'taxrate' => $this->toApiFloat($taxRate),
                'discount' => $this->toApiFloat(0)
            ]);
        }
    }

    /**
     * @param BuilderInterface $checkout
     * @param StoreInterface $store
     *
     * @return float|int
     */
    private function calculateShippingTax($subject, StoreInterface $store)
    {
        $request = $this->taxCalculator->getRateRequest(
            $subject->getShippingAddress(),
            $subject->getBillingAddress(),
            $subject->getCustomerTaxClassId(),
            $store
        );
        $taxRateId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            ScopeInterface::SCOPE_STORES,
            $store
        );

        $request->setProductClassId($taxRateId);

        return $this->taxCalculator->getRate($request);
    }

    /**
     *
     */
    private function toApiFloat($float)
    {
        return (int)round($float * 100);
    }
}
