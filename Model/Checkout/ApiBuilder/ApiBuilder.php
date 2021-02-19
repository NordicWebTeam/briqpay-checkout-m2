<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder;

use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Checkout\DTO\PaymentSession\CreatePaymentSession;
use Briqpay\Checkout\Model\Config\Provider\CheckoutTypeProvider;
use Briqpay\Checkout\Rest\Request\InitializePaymentRequest;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class ApiBuilder
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName = null;

    /**
     * @var OrderLineCollectorsAgreggator
     */
    private $orderLinesAggregator;

    /**
     * @var CheckoutTypeProvider
     */
    private $checkoutTypeProvider;

    /**
     * @var CreatePaymentSession
     */
    private $paymentSessionDTO;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param OrderLineCollectorsAgreggator $orderLinesAggregator
     * @param CheckoutTypeProvider $checkoutTypeProvider
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        OrderLineCollectorsAgreggator $orderLinesAggregator,
        CheckoutTypeProvider $checkoutTypeProvider,
        $instanceName = InitializePaymentRequest::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->orderLinesAggregator = $orderLinesAggregator;
        $this->checkoutTypeProvider = $checkoutTypeProvider;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return InitializePaymentRequest
     */
    public function generateRequest(): InitializePaymentRequest
    {
        return $this->objectManager->create($this->instanceName, [
            'data' => new DataObject(
                $this->paymentSessionDTO->toArray()
            )
        ]);
    }

    /**
     * @param $subject
     */
    public function collect($subject)
    {
        $this->paymentSessionDTO = $this->orderLinesAggregator->aggregateItems($subject);
    }
}
