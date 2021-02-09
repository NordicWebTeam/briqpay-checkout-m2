<?php

namespace Briqpay\Checkout\Logger;

use Magento\Framework\Logger\Handler\Base;

class ErrorHandler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/briqpay.log';
}
