<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\CheckoutSetup;

use Briqpay\Checkout\Model\Config\CheckoutSetup;

class CheckoutSetupProvider
{
    /**
     * @var CheckoutSetup
     */
    private $checkoutSetupConfig;

    /**
     * CheckoutSetupProvider constructor.
     *
     * @param CheckoutSetup $checkoutSetupConfig
     */
    public function __construct(CheckoutSetup $checkoutSetupConfig)
    {
        $this->checkoutSetupConfig = $checkoutSetupConfig;
    }

    /**
     * @param $emailInvoice
     *
     * @return array
     */
    public function getData($emailInvoice)
    {
        return [
            'selectedPaymentMethod' => [
                'type' => $this->checkoutSetupConfig->getPreselectedMethodType()
            ],
            'emailInvoice'                  => $emailInvoice,
            'callbackUrl'                   => $this->checkoutSetupConfig->getCallbackUrl(),
            'completedNotificationUrl'      => $this->checkoutSetupConfig->getWebhookUrl(),
            'recurringPayments'             => $this->checkoutSetupConfig->getRecurringPaymentChecked(),
            'language'                      => $this->checkoutSetupConfig->getCheckoutLanguage(),
            'mode'                          => $this->checkoutSetupConfig->getCheckoutType(),
            'displayItems'                  => $this->checkoutSetupConfig->getIsItemsDisplayed(),
            'smsNewsletterSubscription'     => $this->checkoutSetupConfig->getSmsNewsletterSubscription(),
            'emailNewsletterSubscription'   => $this->checkoutSetupConfig->getEmailNewsletterSubscription(),
            'differentDeliveryAddress'      => $this->checkoutSetupConfig->getDifferentDeliveryAddress(),
            'termsAndConditionsUrl'         => $this->checkoutSetupConfig->getTermsAndConditionsUrl(),
            'itegrityConditionsUrl'         => $this->checkoutSetupConfig->getItegrityConditionsUrl()
        ];
    }
}
