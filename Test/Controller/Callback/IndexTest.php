<?php

namespace Briqpay\Checkout\Test\Controller\Callback;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Briqpay\Checkout\Rest\Adapter\ReadSession;
use Briqpay\Checkout\Controller\Callback\Index;
use Briqpay\Checkout\Rest\Response\GetPaymentStatusResponse;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Encryption\Encryptor;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class IndexTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepo;

    /**
     * @var GetPaymentStatusResponse|MockObject
     */
    private $response;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Index
     */
    private $testSubject;

    public function setUp(): void
    {
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->response = $this->createMock(GetPaymentStatusResponse::class);
        $this->order = $this->createMock(Order::class);
        $this->payment = $this->createMock(Payment::class);

        $request = $this->createMock(Http::class);
        $jsonFactory = $this->createMock(JsonFactory::class);
        $scBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $readSession = $this->createMock(ReadSession::class);
        $encryptor = $this->createMock(Encryptor::class);

        $request->method('getParam')->with('sessionid')->willReturn('');
        $jsonFactory->method('create')->willReturn($this->createMock(Json::class));
        $scBuilder->method('addFilter')->willReturn($scBuilder);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $scBuilder->method('create')->willReturn($searchCriteria);
        $readSession->method('readSession')->willReturn($this->response);
        $this->order->method('getPayment')->willReturn($this->payment);
        $this->order->method('getState')->willReturn(Order::STATE_NEW);
        $this->order->method('getBaseTotalDue')->willReturn(99);
        $this->payment->method('getBaseAmountAuthorized')->willReturn(0);
        $orderCollection = $this->createMock(OrderCollection::class);
        $orderCollection->method('getItems')->willReturn([$this->order]);
        $this->orderRepo->method('getList')->willReturn($orderCollection);

        $this->testSubject = new Index(
            $request,
            $jsonFactory,
            $this->orderRepo,
            $scBuilder,
            $readSession,
            $encryptor
        );
    }

    /**
     * Test that order gets state 'new' when payment state is 'paymentprocessing'
     *
     * @return void
     */
    public function testExecuteWithStatePaymentProcessing()
    {
        $this->order->method('getState')->willReturn(Order::STATE_NEW);
        $this->response->method('getState')->willReturn(Index::PAYMENT_STATE_PAYMENT_PROCESSING);

        $this->order->expects($this->once())->method('setState')->with(Order::STATE_NEW);
        $this->payment->expects($this->never())->method('authorize');
        $this->testSubject->execute();
    }

    /**
     * Test that order gets state 'holded' when payment state is 'purchaserejected'
     *
     * @return void
     */
    public function testExecuteWithStatePurchaseRejected()
    {
        $this->order->method('getState')->willReturn(Order::STATE_NEW);
        $this->response->method('getState')->willReturn(Index::PAYMENT_STATE_PURCHASE_REJECTED);

        $this->order->expects($this->once())->method('setState')->with(Order::STATE_HOLDED);
        $this->payment->expects($this->never())->method('authorize');
        $this->testSubject->execute();
    }

    /**
     * Test that order gets state 'processing' when payment state is 'purchasecomplete'
     *
     * @return void
     */
    public function testExecuteWithStatePurchaseComplete()
    {
        $this->order->method('getState')->willReturn(Order::STATE_NEW);
        $this->response->method('getState')->willReturn(Index::PAYMENT_STATE_PURCHASE_COMPLETE);

        $this->order->expects($this->once())->method('setState')->with(Order::STATE_PROCESSING);
        $this->payment->expects($this->once())->method('authorize');
        $this->testSubject->execute();
    }
}
