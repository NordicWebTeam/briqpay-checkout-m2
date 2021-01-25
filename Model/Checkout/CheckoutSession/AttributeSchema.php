<?php

namespace Briqpay\Checkout\Model\Checkout\CheckoutSession;

interface AttributeSchema
{
    public const PURCHASE_ID = 'briqpay_purchase_id';
    public const JWT         = 'briqpay_jwt';
    public const EXPIRED_UTC = 'briqpay_expired_utc';
}
