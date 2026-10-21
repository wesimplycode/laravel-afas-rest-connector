<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use WeSimplyCode\LaravelAfasRestConnector\Exceptions\AfasAuthenticationException;

/**
 * Retrieves and caches OAuth access tokens using the client credentials flow.
 *
 * @see https://docs.afas.help/profit/en/authentication
 */
class AfasOAuthTokenProvider
{
    /**
     * @var AfasConnection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $options;

    /**
     * Access token for the lifetime of this instance
     * @var string|null
     */
    protected $accessToken;

    /**
     * Unix timestamp at which the in-memory access token expires
     * @var int
     */
    protected $tokenExpires = 0;

    /**
     * Called with (string $accessToken, int $expiresAt) whenever a new token is retrieved
     * @var callable|null
     */
    protected $tokenUpdateCallback;

    public function __construct(AfasConnection $connection, array $options = [])
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * Get a valid access token, from memory or cache when possible.
     *
     * @return string
     * @throws AfasAuthenticationException
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken && !$this->tokenHasExpired()) {
            return $this->accessToken;
        }

        if ($token = $this->getCachedAccessToken()) {
            return $token;
        }

        return $this->acquireAccessToken();
    }

    /**
     * Request a new access token from AFAS, ignoring any cached token.
     *
     * @return string
     * @throws AfasAuthenticationException
     */
    public function requestAccessToken(): string
    {
        $clientId = $this->connection->getClientId();
        $clientSecret = $this->connection->getClientSecret();

        if (!$clientId || !$clientSecret) {
            throw new AfasAuthenticationException("AFAS OAuth client_id and client_secret are required for the client credentials flow.");
        }

        /** @var Response $response */
        $response = Http::asForm()
            ->acceptJson()
            ->post($this->connection->getOAuthTokenUrl(), [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

        if ($response->failed()) {
            throw new AfasAuthenticationException(
                "Could not retrieve an AFAS OAuth access token (HTTP {$response->status()}): ".$response->body(),
                $response->status()
            );
        }

        $accessToken = $response->json('access_token');

        if (!is_string($accessToken) || $accessToken === '') {
            throw new AfasAuthenticationException("AFAS OAuth token response did not contain an access_token.");
        }

        $expiresIn = (int) $response->json('expires_in', 3600);
        $ttl = max(0, $expiresIn - (int) $this->option('expiry_margin', 60));

        $this->storeAccessToken($accessToken, $ttl);

        return $accessToken;
    }

    /**
     * Remove the access token from memory and cache, for example after receiving a 401 response.
     */
    public function forgetAccessToken(): void
    {
        $this->accessToken = null;
        $this->tokenExpires = 0;

        $this->cache()->forget($this->cacheKey());
    }

    /**
     * Register a callback that is invoked with (string $accessToken, int $expiresAt) after a new token is retrieved.
     *
     * @param callable|null $callback
     * @return $this
     */
    public function setTokenUpdateCallback(?callable $callback): AfasOAuthTokenProvider
    {
        $this->tokenUpdateCallback = $callback;

        return $this;
    }

    /**
     * @return int Unix timestamp at which the current in-memory token expires (0 when no token is held)
     */
    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return $this->option('cache_prefix', 'afas_oauth_access_token')
            .':'.$this->connection->getEnvironment()
            .':'.sha1((string) $this->connection->getClientId());
    }

    /**
     * Acquire a token while holding a lock, so concurrent processes only request one token.
     *
     * @return string
     * @throws AfasAuthenticationException
     */
    protected function acquireAccessToken(): string
    {
        $store = $this->cache()->getStore();

        if (!$store instanceof LockProvider) {
            return $this->requestAccessToken();
        }

        $lock = $store->lock($this->cacheKey().':lock', (int) $this->option('lock_seconds', 10));

        try {
            return $lock->block((int) $this->option('lock_wait_seconds', 10), function () {
                // Another process may have stored a token while we were waiting for the lock
                if ($token = $this->getCachedAccessToken()) {
                    return $token;
                }

                return $this->requestAccessToken();
            });
        } catch (LockTimeoutException $e) {
            return $this->requestAccessToken();
        }
    }

    /**
     * @return string|null
     */
    protected function getCachedAccessToken(): ?string
    {
        $cached = $this->cache()->get($this->cacheKey());

        if (is_array($cached) && !empty($cached['access_token']) && is_string($cached['access_token'])) {
            $this->accessToken = $cached['access_token'];
            $this->tokenExpires = (int) ($cached['expires_at'] ?? 0);

            return $this->tokenHasExpired() ? null : $this->accessToken;
        }

        return null;
    }

    /**
     * @param string $accessToken
     * @param int $ttl seconds the token may be used
     */
    protected function storeAccessToken(string $accessToken, int $ttl): void
    {
        $this->accessToken = $accessToken;
        $this->tokenExpires = time() + $ttl;

        if ($ttl > 0) {
            $this->cache()->put($this->cacheKey(), [
                'access_token' => $accessToken,
                'expires_at' => $this->tokenExpires,
            ], $ttl);
        }

        if (is_callable($this->tokenUpdateCallback)) {
            call_user_func($this->tokenUpdateCallback, $accessToken, $this->tokenExpires);
        }
    }

    /**
     * @return bool
     */
    protected function tokenHasExpired(): bool
    {
        return $this->tokenExpires <= time();
    }

    /**
     * @return Repository
     */
    protected function cache(): Repository
    {
        return Cache::store($this->option('cache_store'));
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function option(string $key, $default = null)
    {
        return array_key_exists($key, $this->options) && $this->options[$key] !== null
            ? $this->options[$key]
            : $default;
    }
}
