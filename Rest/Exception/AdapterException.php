<?php

namespace Briqpay\Checkout\Rest\Exception;

class AdapterException extends RestException
{
    /**
     * @param \Exception $cause
     * @return static
     */
    public static function create(\Exception $cause)
    {
        $message = 'API connection failed';

        return new static($cause->getMessage(), $cause->getCode(), $cause);
    }
}
