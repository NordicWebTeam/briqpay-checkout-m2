<?php

namespace Briqpay\Checkout\Model\Quote\ValidationRules;

use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Validation\ValidationResultFactory;
use Briqpay\Checkout\Rest\Adapter\ReadSession;
use Briqpay\Checkout\Model\Checkout\CheckoutSession\SessionManagement;
use Briqpay\Checkout\Model\Checkout\ApiBuilder\OrderLine\OrderLineCollectorsAgreggator;

/**
 * Validate that quote matches Briqpay payment
 */
class MatchesPayment implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $resultFactory;

    /**
     * @var SessionManagement
     */
    private $sessionManagement;

    /**
     * @var ReadSession
     */
    private $readSession;

    /**
     * @var OrderLineCollectorsAgreggator
     */
    private $aggregator;

    /**
     * @var Quote|null
     */
    private $processedQuote;

    public function __construct(
        ValidationResultFactory $resultFactory,
        SessionManagement $sessionManagement,
        ReadSession $readSession,
        OrderLineCollectorsAgreggator $aggregator
    ) {
        $this->resultFactory = $resultFactory;
        $this->sessionManagement = $sessionManagement;
        $this->readSession = $readSession;
        $this->aggregator = $aggregator;
    }

    public function validate(Quote $quote): array
    {
        $result = [$this->resultFactory->create(['errors' => []])];

        if ($quote->getPayment()->getMethod() !== \Briqpay\Checkout\Model\Payment\Briqpay::CODE) {
            return $result;
        }

        $this->processedQuote = $quote;
        if (!$this->compareWithPayment()) {
            return $this->returnError();
        }

        return $result;
    }

    /**
     * Compares quote contets with Briqpay payment contents
     *
     * @return boolean
     */
    private function compareWithPayment(): bool
    {
        $quote = $this->processedQuote;
        $getStatusResponse = $this->readSession->readSession(
            $this->sessionManagement->getSessionId(),
            $this->sessionManagement->getSessionToken()
        );

        $bGrandTotal = (int)$getStatusResponse->getData('amount');
        $qGrandTotal = $this->toApiFloat(
            $quote->getBaseGrandTotal()
        );

        if ($bGrandTotal !== $qGrandTotal) {
            return false;
        }

        $comparisonSession = $this->aggregator->aggregateItems($quote);
        // Sort and compare cart items
        $bItems = $getStatusResponse->getCart();
        $qItems = $comparisonSession->getCart();

        $bItemComparison = [];
        $qItemComparison = [];
        foreach ($bItems->getData() as $bItem) {
            $sku = (string)$bItem['reference'];
            $bItemComparison[$sku] = $bItem['quantity'];
        }

        foreach ($qItems as $qItem) {
            $sku = (string)$qItem['reference'];
            $qItemComparison[$sku] = $qItem['quantity'];
        }

        $diff = array_diff_assoc($qItemComparison, $bItemComparison);
        if (count($diff) > 0) {
            return false;
        }

        return true;
    }

    private function toApiFloat($float): int
    {
        return (int)round($float * 100);
    }

    /**
     * Return invalid status with error message
     *
     * @return array
     */
    private function returnError()
    {
        $error = __(
            'Your cart differs from the payment. '
            . 'Please try placing the order again. '
            . 'Avoid updating your cart in a different tab or window while completing the checkout.'
        );

        $this->processedQuote->addMessage($error);
        return [$this->resultFactory->create(['errors' => [$error]])];
    }
}
