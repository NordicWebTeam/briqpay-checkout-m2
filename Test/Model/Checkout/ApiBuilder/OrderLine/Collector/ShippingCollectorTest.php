<?php

namespace Briqpay\Checkout\Test\Model\Checkout\ApiBuilder\OrderLine\Collector;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\Collector\ShippingCollector;
use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Config as ScopeConfig;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class ShippingCollectorTest extends TestCase
{
    /**
     * Tests collection of order shipping where shipping amounts are zero and in "float string" format,
     * which would cause division by zero issues
     *
     * @return void
     */
    public function testCollectOrderWithDiscountAndFreeShipping()
    {
        $taxCalculator = $this->createMock(Calculation::class);
        $taxCalculator->method('getRateRequest')->willReturn($this->createMock(DataObject::class));
        $taxCalculator->method('getRate')->willReturn(25);

        $scopeConfig = $this->createMock(ScopeConfig::class);
        $scopeConfig->method('getValue')->willReturn(1);

        $taxConfig = $this->createMock(TaxConfig::class);
        $taxConfig->method('discountTax')->willReturn(0);

        $dataObjectFactory = $this->createMock(DataObjectFactory::class);
        $dataObjectFactory->method('create')->willReturnCallback(function () {
            return new DataObject();
        });

        $testSubject = new ShippingCollector(
            $taxCalculator,
            $scopeConfig,
            $taxConfig,
            $dataObjectFactory
        );

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->onlyMethods(
                [
                    'getIsVirtual',
                    'getStore',
                    'getBaseShippingDiscountAmount',
                    'getBaseShippingAmount',
                    'getBaseShippingInclTax',
                    'getBillingAddress',
                    'getShippingAddress'
                ]
            )
            ->addMethods(['getCustomerTaxClassId'])
            ->getMock()
        ;
        $order->method('getIsVirtual')->willReturn(false);
        $order->method('getStore')->willReturn($this->createMock(Store::class));
        $order->method('getBaseShippingDiscountAmount')->willReturn("0.0");
        $order->method('getBaseShippingAmount')->willReturn("0.0");
        $order->method('getBaseShippingInclTax')->willReturn("0.0");
        $order->method('getBillingAddress')->willReturn($this->createMock(Address::class));
        $order->method('getShippingAddress')->willReturn($this->createMock(Address::class));
        $order->method('getCustomerTaxClassId')->willReturn(1);

        $paymentSession = $this->createMock(CreatePaymentSession::class);

        $paymentSession->expects($this->once())->method('addCartItem')->with([
            'producttype' => 'virtual',
            'reference' => 'shipping',
            'name' => __('Shipping & Handling')->getText(),
            'quantity' => 1,
            'quantityunit' => 'pc',
            'unitprice' => 0,
            'taxrate' => 2500,
            'discount' => 0
        ]);
        $testSubject->collect($paymentSession, $order);
    }
}
