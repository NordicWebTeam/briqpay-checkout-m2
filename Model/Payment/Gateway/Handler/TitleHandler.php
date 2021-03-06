<?php

namespace Briqpay\Checkout\Model\Payment\Gateway\Handler;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Model\InfoInterface;

class TitleHandler implements ValueHandlerInterface
{
    const DEFAULT_TITLE        = 'Briqpay';
    const DEFAULT_TITLE_FORMAT = '%s (%s)';

    /**
     * Retrieve method configured value
     *
     * @param array    $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        if (!isset($subject['payment'])) {
            return self::DEFAULT_TITLE;
        }
        /** @var InfoInterface $payment */
        $payment = $subject['payment']->getPayment();
        $title = $this->getTitle($payment);

        return $title;
    }

    /**
     * Get title for specified payment method
     *
     * @param InfoInterface $payment
     * @return string
     */
    public function getTitle($payment)
    {
        if ($payment->hasAdditionalInformation('method_title')) {
            return $payment->getAdditionalInformation('method_title');
        }
        if ($payment->hasAdditionalInformation('method_code')) {
            return sprintf(
                self::DEFAULT_TITLE_FORMAT,
                self::DEFAULT_TITLE,
                $payment->hasAdditionalInformation('method_code')
            );
        }
        return self::DEFAULT_TITLE;
    }
}
