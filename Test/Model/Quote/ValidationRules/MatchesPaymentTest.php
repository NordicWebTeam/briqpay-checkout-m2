<?php

namespace Briqpay\Checkout\Test\Model\Quote\ValidationRules;

use Briqpay\Checkout\Model\Quote\ValidationRules\MatchesPayment;
use Briqpay\Checkout\Rest\Adapter\ReadSession;
use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Framework\Validation\ValidationResult;
use Magento\Quote\Model\Quote;
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
     * @var OrderLineCollectorsAgreggator|MockObject
     */
    private $aggregator;

    /**
     * @var GetPaymentStatusResponse|MockObject
     */
    private $response;

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
            ->onlyMethods(['getPayment', 'getAllItems', 'addMessage'])
            ->addMethods(['getBaseGrandTotal'])
            ->getMock()
        ;

        $this->quote->method('getPayment')->willReturn($this->payment);
        $this->readSession = $this->createMock(ReadSession::class);
        $this->aggregator = $this->createMock(OrderLineCollectorsAgreggator::class);

        $this->resultFactory = $this->createMock(ValidationResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->createMock(ValidationResult::class));
        $this->sessionManagement = $this->createMock(SessionManagement::class);
        $this->testSubject = new MatchesPayment(
            $this->resultFactory,
            $this->sessionManagement,
            $this->readSession,
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
        $this->quote->expects($this->never())->method('addMessage');
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
        $this->quote->expects($this->once())->method('addMessage');
        $this->testSubject->validate($this->quote);
    }
}
