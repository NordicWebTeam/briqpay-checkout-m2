<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\ApiBuilder;

use Briqpay\Checkout\Model\Checkout\CheckoutSetup\CheckoutSetupProvider;
use Briqpay\Checkout\Model\Checkout\OrderLine\OrderLineCollectorsAgreggator;
use Briqpay\Checkout\Model\Config\Provider\CheckoutTypeProvider;
use Briqpay\Checkout\Rest\Request\InitializePaymentRequest;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;

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
     * @var array
     */
    private $orderLines = [];

    /**
     * @var OrderLineCollectorsAgreggator
     */
    private $orderLinesAggregator;

    /**
     * @var CheckoutSetupProvider
     */
    private $checkoutSetupProvider;

    /**
     * @var CheckoutTypeProvider
     */
    private $checkoutTypeProvider;

    /**
     * @var Quote
     */
    private $subject;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param OrderLineCollectorsAgreggator $orderLinesAggregator
     * @param CheckoutSetupProvider $checkoutSetupProvider
     * @param CheckoutTypeProvider $checkoutTypeProvider
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        OrderLineCollectorsAgreggator $orderLinesAggregator,
        CheckoutSetupProvider $checkoutSetupProvider,
        CheckoutTypeProvider $checkoutTypeProvider,
        $instanceName = InitializePaymentRequest::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->orderLinesAggregator = $orderLinesAggregator;
        $this->checkoutSetupProvider = $checkoutSetupProvider;
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
            'data' => new DataObject([
                'items' => $this->orderLines,
                'checkoutSetup' => $this->checkoutSetupProvider->getData($this->subject->getCustomerEmail()),
                $this->checkoutTypeProvider->getData($this->subject)
            ])
        ]);
    }

    /**
     * @return array
     */
    public function getOrderLines()
    {
        return $this->orderLines;
    }

    /**
     * @param $subject
     */
    public function collect($subject)
    {
        $this->subject = $subject;
        $this->orderLines = $this->orderLinesAggregator->aggregateItems($subject);

        return $this;
    }
}
