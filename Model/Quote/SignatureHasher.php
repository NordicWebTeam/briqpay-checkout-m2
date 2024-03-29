<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Quote;

use Magento\Quote\Model\Quote;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class SignatureHasher
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    public function __construct(
        SerializerInterface $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @param Quote $quote
     *
     * @return string
     */
    public function getQuoteSignature(Quote $quote)
    {
        $shippingMethod = null;
        $countryId = null;

        if (!$quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $countryId = $shippingAddress->getCountryId();
            $shippingMethod = $shippingAddress->getShippingMethod();
        }

        $billingAddress = $quote->getBillingAddress();
        $info = [
            'currency'=> $quote->getQuoteCurrencyCode(),
            'shipping_method' => $shippingMethod,
            'shipping_country' => $countryId,
            'billing_country' => $billingAddress->getCountryId(),
            'payment' => $quote->getPayment()->getMethod(),
            'subtotal'=> sprintf("%.2f", round($quote->getBaseSubtotal(), 2)),
            'total'=> sprintf("%.2f", round($quote->getBaseGrandTotal(), 2)),
            'items'=> []
        ];

        foreach ($quote->getAllVisibleItems() as $item) {
            $info['items'][$item->getId()] = sprintf("%.2f", round($item->getQty()*$item->getBasePriceInclTax(), 2));
        }
        ksort($info['items']);

        return $this->encryptor->encrypt($this->serializer->serialize($info));
    }
}
