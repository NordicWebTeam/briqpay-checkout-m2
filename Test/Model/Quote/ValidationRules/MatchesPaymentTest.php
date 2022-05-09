<?php

namespace Briqpay\Checkout\Test\Model\Quote\ValidationRules;

use Briqpay\Checkout\Model\Quote\ValidationRules\MatchesPayment;
use Briqpay\Checkout\Rest\Adapter\ReadSession;
use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Rest\Adapter\CancelAdapter;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Payment;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MatchesPaymentTest extends TestCase
{
    /**
     * @var MatchesPayment
     */
    private $testSubject;

    /**
     * @var ValidationResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var SessionManagement|MockObject
     */
    private $sessionManagement;

    /**
     * @var ReadSession|MockObject
     */
    private $readSession;

    /**
     * @var CancelAdapter|MockObject
     */
    private $cancelAdapter;

    /**
     * @var OrderLineCollectorsAgreggator|MockObject
     */
    private $aggregator;

    /**
     * @var GetPaymentStatusResponse|MockObject
     */
    private $response;

    /**
     * @var DataObject
     */
    private $responseCart;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    public function setUp(): void
    {
        $this->payment = $this->createMock(Payment::class);
        $this->payment->method('getMethod')->willReturn('briqpay');

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->onlyMethods(['getPayment', 'getAllItems'])
            ->addMethods(['getBaseGrandTotal'])
            ->getMock()
        ;

        $this->quote->method('getPayment')->willReturn($this->payment);

        $item1 = $this->createMock(Item::class);
        $item1->method('getSku')->willReturn('sku1');
        $item1->method('getQty')->willReturn(1);

        $item2 = $this->createMock(Item::class);
        $item2->method('getSku')->willReturn('sku2');
        $item2->method('getQty')->willReturn(2);

        $this->quote->method('getAllItems')->willReturn([$item1, $item2]);
        $this->readSession = $this->createMock(ReadSession::class);
        $this->aggregator = $this->createMock(OrderLineCollectorsAgreggator::class);

        $this->resultFactory = $this->createMock(ValidationResultFactory::class);
        $this->sessionManagement = $this->createMock(SessionManagement::class);
        $this->cancelAdapter = $this->createMock(CancelAdapter::class);
        $this->testSubject = new MatchesPayment(
            $this->resultFactory,
            $this->sessionManagement,
            $this->readSession,
            $this->cancelAdapter,
            $this->aggregator
        );
    }

    /**
     * Test the validation when the data matches
     *
     * @return void
     */
    public function testMatchingValidate()
    {
        $this->response = new GetPaymentStatusResponse(new DataObject([
            'amount' => 1000,
            'cart' => [
                [
                    'reference' => 'sku1',
                    'quantity' => 1
                ],
                [
                    'reference' => 'sku2',
                    'quantity' => 2
                ]
            ]
        ]));
        $this->readSession->method('readSession')->willReturn($this->response);
        $paymentSession = $this->createMock(CreatePaymentSession::class);
        $paymentSession->method('getCart')->willReturn([
            [
                'reference' => 'sku1',
                'quantity' => 1
            ],
            [
                'reference' => 'sku2',
                'quantity' => 2
            ]
        ]);
        $this->quote->method('getBaseGrandTotal')->willReturn(10.00);
        $this->aggregator->method('aggregateItems')->willReturn($paymentSession);
        $this->resultFactory->expects($this->once())->method('create')->with(['errors' => []]);
        $this->testSubject->validate($this->quote);
    }

    /**
     * Test the validation when the data doesn't match
     *
     * @return void
     */
    public function testNotMatchingValidate()
    {
        $this->response = new GetPaymentStatusResponse(new DataObject([
            'amount' => 1000,
            'cart' => [
                [
                    'reference' => 'sku1',
                    'quantity' => 1
                ],
                [
                    'reference' => 'sku2',
                    'quantity' => 2
                ]
            ]
        ]));
        $this->readSession->method('readSession')->willReturn($this->response);
        $paymentSession = $this->createMock(CreatePaymentSession::class);
        $paymentSession->method('getCart')->willReturn([
            [
                'reference' => 'sku1',
                'quantity' => 1
            ],
            [
                'reference' => 'sku2',
                'quantity' => 3
            ]
        ]);
        $this->quote->method('getBaseGrandTotal')->willReturn(15.00);
        $this->aggregator->method('aggregateItems')->willReturn($paymentSession);
        $this->cancelAdapter->expects($this->atLeastOnce())->method('cancel');
        $this->testSubject->validate($this->quote);
    }
}
