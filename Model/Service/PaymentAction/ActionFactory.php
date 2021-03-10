<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Service\PaymentAction;

use Briqpay\Checkout\Api\PaymentStatusInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

class ActionFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Briqpay\Checkout\Model\Config\Payment
     */
    private $paymentConfig;

    /**
     * Factory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Briqpay\Checkout\Model\Config\Payment $paymentConfig
    ) {
        $this->objectManager = $objectManager;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * @param $isCaptured
     *
     * @return PaymentActionInterface
     */
    public function get($isCaptured)
    {
        if ($isCaptured) {
            return $this->objectManager->create(CaptureInvoice::class);
        }

        return $this->objectManager->create(Authorize::class, [
            'capturePayment' => $this->paymentConfig->isAutoCapture()
        ]);
    }
}
