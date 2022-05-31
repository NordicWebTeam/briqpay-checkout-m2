<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderItemCollectorInterface;
use Klarna\Core\Api\BuilderInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;
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
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * ShippingCollector constructor.
     *
     * @param Calculation $taxCalculator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Calculation $taxCalculator,
        ScopeConfigInterface $scopeConfig,
        TaxConfig $taxConfig,
        DataObjectFactory $dataObjectFactory
    )
    {
        $this->taxCalculator = $taxCalculator;
        $this->scopeConfig = $scopeConfig;
        $this->taxConfig = $taxConfig;
        $this->dataObjectFactory = $dataObjectFactory;
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
                /** @var Address $address */
                $address = $subject->getShippingAddress();

                $taxRate = $this->calculateShippingTax($subject, $store);

                $discountPercent = $this->calculateShippingDiscountPercent($subject);

                $paymentSession->addCartItem([
                    'producttype' => 'virtual',
                    'reference' => 'shipping',
                    'name' => __('Shipping & Handling')->getText(),
                    'quantity' => 1,
                    'quantityunit' => 'pc',
                    'unitprice' => $this->toApiFloat($address->getBaseShippingAmount()),
                    'taxrate' => $this->toApiFloat($taxRate),
                    'discount' => $this->toApiFloat($discountPercent)
                ]);

                $amount = $this->toApiFloat(
                    $address->getBaseShippingInclTax()
                );
                $paymentSession->addAmount($amount);
            }
        }

        if (($subject instanceof Order) && !$subject->getIsVirtual()) {
            $discountPercent = $this->calculateShippingDiscountPercent($subject);
            $taxRate = $this->calculateShippingTax($subject, $subject->getStore());
            //$taxAmount = $subject->getShippingTaxAmount() + $object->getShippingHiddenTaxAmount();

            $paymentSession->addCartItem([
                'producttype' => 'virtual',
                'reference' => 'shipping',
                'name' => __('Shipping & Handling')->getText(),
                'quantity' => 1,
                'quantityunit' => 'pc',
                'unitprice' => $this->toApiFloat($subject->getBaseShippingAmount()),
                'taxrate' => $this->toApiFloat($taxRate),
                'discount' => $this->toApiFloat($discountPercent)
            ]);

            $amount = $this->toApiFloat(
                $subject->getBaseShippingInclTax()
            );
            $paymentSession->addAmount($amount);
        }
    }

    /**
     * Calculate shipping discount percent based on subject type and tax configuration
     *
     * @param Quote|Order $subject
     * @return int
     */
    private function calculateShippingDiscountPercent($subject)
    {
        $info = $this->getSubjectShippingInfo($subject);

        $discountAmount = $info->getDiscountAmount();

        if (!$discountAmount) {
            return 0;
        }

        $amountExclTax = $info->getAmountExclTax();
        $amountInclTax = $info->getAmountInclTax();

        $store = $subject->getStore();
        $discountTax = $this->taxConfig->discountTax($store->getId());
        $discountBase = ($discountTax) ? $amountInclTax : $amountExclTax;

        if (!$discountBase) {
            return 0;
        }

        return ($discountAmount / $discountBase) * 100;
    }

    /**
     * Gets data object with shipping info from calculation subject
     *
     * @param Quote|Order $subject
     * @return DataObject
     */
    private function getSubjectShippingInfo($subject): DataObject
    {
        if ($subject instanceof Quote) {
            return $this->getQuoteShippingInfo($subject);
        }

        $info = $this->dataObjectFactory->create()->setData([
            'discount_amount' => 0,
            'amount_excl_tax' => 0,
            'amount_incl_tax' => 0
        ]);

        if ($subject instanceof Order) {
            $info->setDiscountAmount($subject->getBaseShippingDiscountAmount());
            $info->setAmountExclTax($subject->getBaseShippingAmount());
            $info->setAmountInclTax($subject->getBaseShippingInclTax());
        }

        return $info;
    }

    /**
     * Helper function to get shipping info from a Quote's Shipping Addresss
     *
     * @param Quote $quote
     * @return DataObject
     */
    private function getQuoteShippingInfo(Quote $quote): DataObject
    {
        $address = $quote->getShippingAddress();
        return $this->dataObjectFactory->create()->setData([
            'discount_amount' => $address->getBaseShippingDiscountAmount(),
            'amount_excl_tax' => $address->getBaseShippingAmount(),
            'amount_incl_tax' => $address->getBaseShippingInclTax()
        ]);
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
