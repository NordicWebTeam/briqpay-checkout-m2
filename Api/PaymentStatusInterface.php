<?php

namespace Briqpay\Checkout\Api;

interface PaymentStatusInterface
{
    public const TYPE_CARD = 'Card';
    public const TYPE_INVOICE = 'MerchantInvoice';
    public const TYPE_EDI_INVOICE = 'Testedi';
    public const TYPE_EDI_FORTNOX = 'Fortnox';
}
