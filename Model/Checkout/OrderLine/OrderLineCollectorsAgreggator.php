<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\OrderLine;

class OrderLineCollectorsAgreggator
{
    /**
     * @var array
     */
    private $orderItemsCollectors;

    /**
     * @var
     */
    private $orderLines = [];

    /**
     * OrderLineCollectorsAgregator constructor.
     */
    public function __construct(array $orderItemsCollectors)
    {
        $this->orderItemsCollectors = $orderItemsCollectors;
    }

    /**
     *
     */
    public function resetOrderLines()
    {
        $this->orderLines = [];
    }

    /**
     * @return array
     */
    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function addOrderLine(array $orderLine)
    {
        $this->orderLines[] = $orderLine;
    }

    /**
     * @param $subject
     */
    public function aggregateItems($subject)
    {
        $this->resetOrderLines();

        /** @var OrderItemCollectorInterface $collector */
        foreach ($this->orderItemsCollectors as $collector) {
            $collector->collect($this, $subject);
        }

        return $this->orderLines;
    }
}
