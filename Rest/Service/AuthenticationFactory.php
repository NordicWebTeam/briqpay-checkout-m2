<?php

namespace Briqpay\Checkout\Rest\Service;

use Magento\Framework\ObjectManagerInterface;

class AuthenticationFactory
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
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = AuthentificationInterface::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     *
     * @return AuthentificationInterface
     */
    public function create(array $data = []) : AuthentificationInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
