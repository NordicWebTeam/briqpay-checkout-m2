<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote\CustomerDataAssigner;

use Briqpay\Checkout\Model\Quote\CustomerDataAssignerInterface;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Factory constructor.
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $type
     *
     * @return CustomerDataAssignerInterface
     */
    public function create($type = CustomerDataAssignerInterface::TYPE_GUEST) : CustomerDataAssignerInterface
    {
        switch ($type) {
            case CustomerDataAssignerInterface::TYPE_CUSTOMER:
                return $this->objectManager->create(Customer::class);
                break;
            case CustomerDataAssignerInterface::TYPE_GUEST:
                return $this->objectManager->create(Guest::class);
                break;
        }
    }
}
