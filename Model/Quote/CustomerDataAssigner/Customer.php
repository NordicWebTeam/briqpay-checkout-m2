<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote\CustomerDataAssigner;

use Briqpay\Checkout\Model\Quote\CustomerDataAssignerInterface;
use Magento\Quote\Model\Quote;

class Customer implements CustomerDataAssignerInterface
{
    public function assignData(Quote $quote): void
    {
        $customer = $quote->getCustomer();
        $quote->setCheckoutMethod(CustomerDataAssignerInterface::TYPE_CUSTOMER)
            ->setCustomerId($customer->getId())
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerIsGuest(false);
    }
}
