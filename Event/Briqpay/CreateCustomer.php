<?php declare(strict_types=1);

namespace Briqpay\Checkout\Event\Briqpay;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderSaveAfter
 *
 * @package Briqpay\Checkout\Event\Briqpay
 */
class CreateCustomer implements ObserverInterface
{
    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutConfig;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    private $customerManagement;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    private $customerRepository;

    /**
     * OrderSaveAfter constructor.
     */
    public function __construct(
       \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutConfig,
       \Magento\Sales\Api\OrderCustomerManagementInterface $customerManagement,
       \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository

    ) {
        $this->checkoutConfig = $checkoutConfig;
        $this->customerManagement = $customerManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($order->getPayment()->getMethod() !== \Briqpay\Checkout\Model\Payment\Briqpay::CODE ||
            ! $this->checkoutConfig->getRegisterOnCheckout()
        ) {
            return;
        }

        try {
            $newCustomer = $this->customerManagement->create($order->getId());
            $newCustomer->setCustomAttribute('briqpay_customer_token', $quote->getBriqpayCustomerToken());
            $this->customerRepository->save($newCustomer);
        } catch (\Exception $e) {}
    }
}
