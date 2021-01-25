<?php

namespace Briqpay\Checkout\Rest\Request;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;

/**
 * Factory class for @see \Briqpay\Checkout\Rest\Request\AuthRequest
 */
class InitializePaymentRequestFactory
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
     * @var \Magento\Quote\Api\Data\CartItemInterface[]
     */
    private $items = [];

    /**
     * @var
     */
    private $checkoutType = [];

    /**
     * @var
     */
    private $extraIdentifiers = [];

    /**
     * @var \Briqpay\Checkout\Model\Config\CheckoutSetup
     */
    private $checkoutSetupConfig;

    /**
     * @var \Briqpay\Checkout\Model\Config\Provider\CheckoutTypeProvider
     */
    private $checkoutTypeProvider;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Briqpay\Checkout\Model\Config\CheckoutSetup $checkoutSetupConfig,
        \Briqpay\Checkout\Model\Config\Provider\CheckoutTypeProvider $checkoutTypeProvider,
        $instanceName = InitializePaymentRequest::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->checkoutSetupConfig = $checkoutSetupConfig;
        $this->checkoutTypeProvider = $checkoutTypeProvider;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param mixed $checkoutType
     */
    public function setCheckoutType($checkoutType): void
    {
        $this->checkoutType = $checkoutType;
    }

    /**
     * @param mixed $extraIdentifiers
     */
    public function setExtraIdentifiers($extraIdentifiers): void
    {
        $this->extraIdentifiers = $extraIdentifiers;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param Quote $quote
     *
     * @return InitializePaymentRequest
     */
    public function create(Quote $quote): InitializePaymentRequest
    {
        $data = new DataObject([
            'items' => $this->getItems($quote),
            "checkoutSetup" => $this->getCheckoutSetup($quote->getCustomerEmail()),
            $this->checkoutTypeProvider->getData($quote)
        ]);

        return $this->objectManager->create(
            $this->instanceName,
            [
                'data' => $data
            ]
        );
    }

    /**
     * @return array
     */
    private function getCheckoutSetup($emailInvoice) : array
    {
        return [
           'selectedPaymentMethod' => [
                'type' => $this->checkoutSetupConfig->getPreselectedMethodType()
           ],
            'callbackUrl'                   => $this->checkoutSetupConfig->getCallbackUrl(),
            'completedNotificationUrl'      => $this->checkoutSetupConfig->getWebhookUrl(),
            'recurringPayments'             => $this->checkoutSetupConfig->getRecurringPaymentChecked(),
            'emailInvoice'                  => $emailInvoice,
            "language"                      => $this->checkoutSetupConfig->getCheckoutLanguage(),
            "mode"                          => $this->checkoutSetupConfig->getCheckoutType(),
            "displayItems"                  => $this->checkoutSetupConfig->getIsItemsDisplayed(),
            "smsNewsletterSubscription"     => $this->checkoutSetupConfig->getSmsNewsletterSubscription(),
            "emailNewsletterSubscription"   => $this->checkoutSetupConfig->getEmailNewsletterSubscription(),
            "differentDeliveryAddress"      => $this->checkoutSetupConfig->getDifferentDeliveryAddress(),
            "termsAndConditionsUrl"         => $this->checkoutSetupConfig->getTermsAndConditionsUrl(),
            "itegrityConditionsUrl"         => $this->checkoutSetupConfig->getItegrityConditionsUrl()
        ];
    }

    /**
     * Calculate rated tax abount based on price and tax rate.
     * If you are using price including tax $priceIncludeTax should be true.
     *
     * @param   float $price
     * @param   float $taxRate
     * @param   boolean $priceIncludeTax
     * @return  float
     */
    public function calcTaxAmount($price, $taxRate, $priceIncludeTax = false)
    {
        $taxRate = $taxRate / 100;

        if ($priceIncludeTax) {
            $amount = $price * (1 - 1 / (1 + $taxRate));
        } else {
            $amount = $price * $taxRate;
        }

        return $amount;
    }

    /**
     * @return array
     */
    private function getItems(Quote $quote) : array
    {
        $data = [];
        $items = $quote->getAllVisibleItems();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $itemAmount = round($item->getTotal() * $item->getQty() - $item->getDiscountAmount(), 2);
            $data[] = [
                'description' => $item->getName(),
                'amount'      => $itemAmount + $item->getTaxAmount(),
                'taxAmount'   => $item->getTaxAmount(),
                'taxCode'     => ($item->getTaxPercent() > 0) ? $item->getTaxPercent() : ($item->getBaseTaxAmount() / $item->getBaseRowTotal() * 100),
                'notes'       => 'notes'
            ];
        }

        /** @var \Magento\Quote\Model\Quote\Address\Total $shipping */
        if ($shipping = $quote->getShippingAddress()) {
            $data[] = [
                'description'   => 'Shipping',
                'amount'        => round($shipping->getShippingInclTax(), 2),
                'taxAmount'     => round($shipping->getShippingTaxAmount(), 2),
                'taxCode'       => $shipping->getShippingInclTax() == 0 ? 0 : ($shipping->getShippingTaxAmount() / $shipping->getShippingInclTax()),
                'notes'         => 'notes'
            ];
        }

       /* if ($discount = $this->getDiscountAmount($quote)) {
            $data[] = [
                'description'   => 'Discount',
                'amount'        => round($discount, 2),
                'taxAmount'     => 0,
                'taxCode'       => 'SE',
                'notes'         => 'notes'
            ];
        }*/

        return $data;
    }

    /**
     * @param Quote $quote
     *
     * @return int
     */
    private function getDiscountAmount(Quote $quote)
    {
        $totals = $quote->isVirtual()
            ? $quote->getBillingAddress()->getTotals()
            : $quote->getShippingAddress()->getTotals();

        /** @var \Magento\Quote\Model\Quote\Address\Total $discount */
        if ($discount = $totals['discount'] ?? null) {
            return $discount->getValue();
        }

        return 0;
    }
}
