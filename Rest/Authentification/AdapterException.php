<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Authentification;

class AdapterException extends \Exception
{
    /**
     * @param \Exception $cause
     *
     * @return \Briqpay\Checkout\Rest\Authentification\AdapterException
     */
    public static function create(\Exception $cause)
    {
        $message = 'API connection failed';

        return new static($message, $cause->getCode(), $cause);
    }
}
