<?php declare(strict_types=1);

namespace Briqpay\Checkout\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

class CartExtensionAttributesLoad
{
    /**
     * @var CartExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param CartExtensionFactory $extensionFactory
     */
    public function __construct(CartExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $cart
     *
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $cartRepository, CartInterface $cart)
    {
        if ($sessionId = $cart->getData(\Briqpay\Checkout\Setup\QuoteSchema::SESSION_ID)) {
            $cart->getExtensionAttributes()->setBriqpaySessionId($sessionId);
        }

        if ($sessionToken = $cart->getData(\Briqpay\Checkout\Setup\QuoteSchema::SESSION_TOKEN)) {
            $cart->getExtensionAttributes()->setBriqpaySessionToken($sessionToken);
        }

        return $cart;
    }
}
