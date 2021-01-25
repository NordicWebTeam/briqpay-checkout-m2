<?php

namespace Briqpay\Checkout\Model\Payment\Gateway\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionValidator extends AbstractValidator
{
    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param StoreManagerInterface  $storeManager
     * @param ScopeConfigInterface   $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config
    ) {
        parent::__construct($resultFactory);
        $this->store = $storeManager->getStore();
        $this->config = $config;
    }

    /**
     * Validate
     *
     * @return ResultInterface
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function validate(array $validationSubject)
    {
        return $this->createResult(true);
    }
}
