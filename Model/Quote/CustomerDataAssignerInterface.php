<?php

namespace Briqpay\Checkout\Model\Quote;

use Magento\Quote\Model\Quote;

interface CustomerDataAssignerInterface
{
    public const TYPE_CUSTOMER = 'customer';

    public const TYPE_GUEST = 'guest';

    /**
     * @param Quote $quote
     */
    public function assignData(Quote $quote) : void;
}
