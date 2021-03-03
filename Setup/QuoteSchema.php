<?php

namespace Briqpay\Checkout\Setup;

interface QuoteSchema
{
    const TABLE = 'quote';
    const SESSION_ID = 'briqpay_session_id';
    const SESSION_TOKEN = 'briqpay_cart_token';
    const CART_SIGNATURE = 'briqpay_cart_signature';
}
