<?php
namespace Briqpay\Checkout\Rest\Request;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Briqpay\Checkout\Rest\Request\AuthRequest
 */
class AuthRequestFactory
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
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = AuthRequest::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     *
     * @throw InvalidArgumentException
     * @return AuthRequest
     */
    public function create(array $data = []): AuthRequest
    {
        if (empty($data['clientId']) || empty($data['clientSecret'])) {
            throw new \InvalidArgumentException('Missing auth credentials');
        }

        return $this->objectManager->create($this->instanceName, $data);
    }
}
