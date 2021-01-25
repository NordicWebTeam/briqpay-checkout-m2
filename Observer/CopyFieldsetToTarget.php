<?php declare(strict_types=1);

namespace Briqpay\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CopyFieldsetToTarget implements ObserverInterface
{
    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     */
    public function __construct(\Magento\Framework\DataObject\Copy $objectCopyService)
    {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');

        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        $this->instantiateExtensionAttributes($quote, $order);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function instantiateExtensionAttributes(
        \Magento\Quote\Api\Data\CartInterface $cart,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $purcahseId = $cart->getExtensionAttributes()->getBriqpayPurchaseId();
        if (! $purcahseId) {
            return;
        }

        $order->getExtensionAttributes()->setBriqpayPurchaseId($purcahseId);
        $order->setBriqpayPurchaseId($purcahseId);
    }
}
