<?php

namespace Briqpay\Checkout\Model\Checkout\Validation;

use Briqpay\Checkout\Model\Checkout\CheckoutException;

interface CheckoutValidatorInterface
{
    /**
     * @throws CheckoutException
     */
    public function validate() : void;
}
