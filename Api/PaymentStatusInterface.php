<?php

namespace Briqpay\Checkout\Api;

interface PaymentStatusInterface
{
    public const TYPE_CARD = 'Card';
    public const TYPE_MASTERCARD = 'Mastercard';
    public const TYPE_LOAN = 'Loan';
    public const TYPE_INVOICE = 'Invoice';
    public const TYPE_SWISH = 'Swish';
}
