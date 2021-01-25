<?php

namespace Briqpay\Checkout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

interface QuoteSchema
{
    const TABLE = 'quote';
    const PURCHASE_ID = 'briqpay_purchase_id';
    const QUOTE_SIGNATURE = 'briqpay_quote_signature';
}
