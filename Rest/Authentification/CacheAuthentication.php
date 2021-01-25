<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Authentification;

use Briqpay\Checkout\Rest\Authentification\Cache\AuthentificationCache;
use Briqpay\Checkout\Rest\Service\AuthentificationInterface;

/**
 * Class CacheAuthentication
 *
 * @package Briqpay\Checkout\Rest\Authentication
 */
class CacheAuthentication implements AuthentificationInterface
{
    /**
     * Cache expiration in seconds, set to 1 hour
     *
     * @var int
     */
    const CACHE_EXPIRATION_TIME = 3600;

    /**
     * @var AuthentificationInterface
     */
    private $authentication;

    /**
     * @var AuthentificationCache
     */
    private $cacheInstance;

    /**
     * CacheAuthentication constructor.
     *
     * @param AuthentificationInterface $authentication
     * @param AuthentificationCache $cacheInstance
     */
    public function __construct(
        AuthentificationInterface $authentication,
        AuthentificationCache $cacheInstance
    ) {
        $this->authentication = $authentication;
        $this->cacheInstance = $cacheInstance;
    }

    /**
     * If cache session found, we don't handle new one
     *
     * @param null $websiteId
     *
     * @throws AdapterException
     */
    public function authenticate($websiteId = null) : void
    {
        if (! $this->cacheInstance->test(AuthentificationCache::TYPE_IDENTIFIER . $websiteId)) {
            $this->authentication->authenticate($websiteId);
            $this->cacheAuthentificationSession(AuthentificationCache::TYPE_IDENTIFIER . $websiteId, self::CACHE_EXPIRATION_TIME);
        }
    }

    /**
     * Get session token
     *
     * @return string|void
     */
    public function getToken() : string
    {
        if ($sessionCache = $this->cacheInstance->load(AuthentificationCache::TYPE_IDENTIFIER)) {
            if ($decodedSessionCache = json_decode($sessionCache, true)) {
                return $decodedSessionCache['session'];
            }
        }

        return $this->authentication->getToken();
    }

    /**
     * Get session token expiry
     *
     * @return string|void
     */
    public function getTokenExpiry() : string
    {
        if ($sessionCache = $this->cacheInstance->load(AuthentificationCache::TYPE_IDENTIFIER)) {
            $sessionCache = json_decode($sessionCache, true);
            return $sessionCache['token_expiry'];
        }

        return $this->authentication->getTokenExpiry();
    }

    /**
     * @param $cacheIdentifier
     * @param $expirationTime
     *
     * @throws AdapterException
     */
    private function cacheAuthentificationSession($cacheIdentifier, $expirationTime) : void
    {
        $this->cacheInstance->save(
            json_encode([
                'session'      => $this->authentication->getToken(),
                'token_expiry' => $this->authentication->getTokenExpiry()
            ]),
            $cacheIdentifier,
            [$cacheIdentifier],
            $expirationTime
        );
    }
}
