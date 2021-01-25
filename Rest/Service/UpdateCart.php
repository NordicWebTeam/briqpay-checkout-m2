<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Model\Config\ApiConfig;
use Briqpay\Checkout\Rest\Adapter\UpdateCart as UpdateCartAdapter;
use Briqpay\Checkout\Rest\Exception\UpdateCartException;

class UpdateCart
{
    /**
     * @var UpdateCartAdapter
     */
    private $updateCartAdapter;

    /**
     * UpdateCart constructor.
     *
     * @param ApiConfig $config
     * @param UpdateCartAdapter $updateCartAdapter
     */
    public function __construct(
        ApiConfig $config,
        UpdateCartAdapter $updateCartAdapter
    ) {
        $this->config = $config;
        $this->updateCartAdapter = $updateCartAdapter;
    }

    /**
     * @param array $items
     * @param $purchaseId
     * @param $accessToken
     *
     * @throws UpdateCartException
     */
    public function updateItems(array $items, $purchaseId, $accessToken) : void
    {
        $this->updateCartAdapter->updateItems($items, $purchaseId, $accessToken);
    }
}
