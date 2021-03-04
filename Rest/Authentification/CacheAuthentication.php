<?php declare(strict_types=1);

namespace Briqpay\Checkout\Rest\Authentification;

use Briqpay\Checkout\Rest\Authentification\Cache\AuthentificationCache;
use Briqpay\Checkout\Rest\Service\AuthentificationInterface;

/**
 * Class CacheAuthentication
 */
class CacheAuthentication implements AuthentificationInterface
{
    /**
     * Cache expiration in seconds, set to 12 hours
     *
     * @var int
     */
    const CACHE_EXPIRATION_TIME = 60 * 60 * 12;

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
    public function authenticate($websiteId = null): string
    {
        $cacheKey = AuthentificationCache::TYPE_IDENTIFIER . $websiteId;
        if (!$this->cacheInstance->test($cacheKey)) {
            $token = $this->authentication->authenticate($websiteId);
            $this->cacheAuthentificationSession($token, $token, self::CACHE_EXPIRATION_TIME);
            return $token;
        }

        if ($token = $this->getFromCache($cacheKey)) {
            return $token;
        }

        throw new AdapterException('Unable to authenticate');
    }

    private function getFromCache($cacheKey): ?string
    {
        if ($sessionCache = $this->cacheInstance->load($cacheKey)) {
            if ($decodedSessionCache = json_decode($sessionCache, true)) {
                return $decodedSessionCache['session'];
            }
        }

        return null;
    }

    /**
     * @param $cacheIdentifier
     * @param $expirationTime
     *
     * @throws AdapterException
     */
    private function cacheAuthentificationSession($httpToken, $cacheIdentifier, $expirationTime): void
    {
        $this->cacheInstance->save(
            json_encode([
                'session' => $httpToken
            ]),
            $cacheIdentifier,
            [$cacheIdentifier],
            $expirationTime
        );
    }
}
