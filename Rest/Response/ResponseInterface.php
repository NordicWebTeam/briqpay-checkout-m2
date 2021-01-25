<?php

namespace Briqpay\Checkout\Rest\Response;

interface ResponseInterface
{
    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getData($key = '');
}
