<?php
namespace Briqpay\Checkout\Rest\Webservice;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Briqpay\Checkout\Rest\Webservice\HttpClient
 */
class HttpClientFactory
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
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = HttpClient::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return HttpClient
     */
    public function create(array $data = []) : HttpClient
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
