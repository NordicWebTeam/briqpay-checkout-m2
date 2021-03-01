<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Schema;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class Parser
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Schema constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Convert JSON document into internal types.
     *
     * @param $data
     * @param $type
     *
     * @return mixed The object with populated properties
     */
    public function parse($data, $type)
    {
        $dataStream = is_string($data) ? json_decode($data, true) : $data;
        $properties = $this->handleDataObject($dataStream);

        return $this->objectManager->create($type, ['data' => $properties]);
    }

    /**
     * @param $data
     *
     * @return DataObject
     */
    private function handleDataObject($data)
    {
        return new DataObject($data);
    }
}
