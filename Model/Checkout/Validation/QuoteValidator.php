<?php declare(strict_types=1);

namespace Briqpay\Checkout\Model\Checkout\Validation;

use Briqpay\Checkout\Model\Checkout\CheckoutException;

class QuoteValidator implements CheckoutValidatorInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * QuoteValidator constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @throws CheckoutException
     */
    public function validate(): void
    {
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            throw new CheckoutException('Can not start checkout', $e->getCode(), $e);
        }

        if (empty($quote->getItems())) {
            throw new CheckoutException('You have no items in your shopping cart.');
        }
    }
}
