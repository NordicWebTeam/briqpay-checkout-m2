<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote\CustomerDataAssigner;

use Briqpay\Checkout\Model\Quote\CustomerDataAssignerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Quote\Model\Quote;

class Guest implements CustomerDataAssignerInterface
{
    /**
     * @var \Briqpay\Checkout\Model\Config\Checkout
     */
    private $checkoutConfig;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

    /**
     * Guest constructor.
     */
    public function __construct(
        \Briqpay\Checkout\Model\Config\Checkout $checkoutConfig,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    )
    {
        $this->checkoutConfig = $checkoutConfig;
        $this->accountManagement = $accountManagement;
    }

    /**
     * @param Quote $quote
     */
    public function assignData(Quote $quote): void
    {
        $billingAddress = $quote->getBillingAddress();
        $quote->setCheckoutMethod(CustomerDataAssignerInterface::TYPE_GUEST)
            ->setCustomerId(null)
            ->setCustomerEmail($billingAddress->getEmail())
            ->setCustomerFirstname($billingAddress->getFirstname())
            ->setCustomerLastname($billingAddress->getLastname())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        // Register the customer, if its required, the customer will then be registered after order is placed
        if ($billingAddress->getEmail() && $this->checkoutConfig->isRegisterOnCheckout()) {
            if (! $this->accountManagement->isEmailAvailable($billingAddress->getEmail(), $quote->getStore()->getWebsiteId())) {
                $quote->setCreateAccountAfterCheckout(true);
            }
        }
    }
}
