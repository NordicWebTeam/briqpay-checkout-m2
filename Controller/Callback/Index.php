<?php

namespace Briqpay\Checkout\Controller\Callback;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Controller\Result\JsonFactory;
use Briqpay\Checkout\Rest\Adapter\ReadSession;
use Magento\Framework\Encryption\EncryptorInterface;

class Index implements HttpGetActionInterface
{
    const PAYMENT_STATE_PURCHASE_REJECTED = 'purchaserejected';

    const PAYMENT_STATE_PURCHASE_COMPLETE = 'purchasecomplete';

    const PAYMENT_STATE_PAYMENT_PROCESSING = 'paymentprocessing';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $scBuilder;

    /**
     * @var ReadSession
     */
    private $readSession;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        Http $request,
        JsonFactory $jsonFactory,
        OrderRepositoryInterface $orderRepo,
        SearchCriteriaBuilder $scBuilder,
        ReadSession $readSession,
        EncryptorInterface $encryptor
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->orderRepo = $orderRepo;
        $this->scBuilder = $scBuilder;
        $this->readSession = $readSession;
        $this->encryptor = $encryptor;
    }

    /**
     * Load order with provided sessionId
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $sessionId = $this->request->getParam('sessionid');
        $order = $this->loadOrder($sessionId);

        if (null === $order) {
            $result->setHttpResponseCode(500);
            $result->setData([
                'status' => 'error',
                'message' => 'Order with provided sessionid not found'
                
            ]);
            return $result;
        }

        try {
            $token = $this->encryptor->decrypt(
                $order->getPayment()->getAdditionalInformation()['briqpay_session_token'] ?? ''
            );
            $getStatusResponse = $this->readSession->readSession(
                $sessionId,
                $token
            );
            $this->handleState($order, $getStatusResponse->getState());
            $result->setData(['status' => 'ok']);
            return $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
            $result->setData([
                'status' => 'error',
                'message' => sprintf(
                    'Unable to retrieve payment state for order. Message: "%s"',
                    $e->getMessage()
                )
            ]);
            return $result;
        }
    }

    /**
     * Load order by sessionId
     *
     * @param string $sessionId
     * @return Order|null
     */
    private function loadOrder(string $sessionId): ?Order
    {
        $searchCriteria = $this->scBuilder->addFilter('briqpay_session_id', $sessionId)->create();
        $result = $this->orderRepo->getList($searchCriteria);
        $orders = $result->getItems();
        return array_shift($orders);
    }

    /**
     * Updates order state and status depending on briqpay payment state.
     * Authorizes payment on state 'purchasecomplete'
     *
     * @param Order $order
     * @param string $briqpayState
     * @return void
     */
    private function handleState(Order $order, string $briqpayState)
    {
        $comment = 'Received callback. Briqpay Payment state was: ' . $briqpayState;

        if ($briqpayState === self::PAYMENT_STATE_PURCHASE_REJECTED) {
            $order->setState(Order::STATE_HOLDED);
        }

        if ($briqpayState === self::PAYMENT_STATE_PURCHASE_COMPLETE) {
            $order->setState(Order::STATE_PROCESSING);
            if ($order->getPayment()->getBaseAmountAuthorized() < $order->getBaseTotalDue()) {
                $payment = $order->getPayment();
                /** @var Payment $payment */
                $payment->authorize(true, $order->getBaseTotalDue());
            }
        }

        if ($briqpayState === self::PAYMENT_STATE_PAYMENT_PROCESSING &&
            $order->getState() !== Order::STATE_PROCESSING &&
            $order->getState() !== Order::STATE_HOLDED
        ) {
            $order->setState(Order::STATE_NEW);
        }

        $order->addCommentToStatusHistory($comment);
        $this->orderRepo->save($order);
    }
}
