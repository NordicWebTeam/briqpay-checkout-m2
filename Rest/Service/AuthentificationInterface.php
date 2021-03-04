<?php

namespace Briqpay\Checkout\Rest\Service;

use Briqpay\Checkout\Rest\Authentification\AdapterException;

interface AuthentificationInterface
{
    const SESSION_THRESHOLD = 3600;

    const DATA_KEY_SESSION_TOKEN = 'briqpay_api_token';

    /**
     * @param null $websiteId
     *
     * @return void
     * @throws AdapterException
     */
    public function authenticate($websiteId = null): string;
}
