<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasConnection
{
    const AUTH_TOKEN = 'token';
    const AUTH_OAUTH = 'oauth';

    /**
     * The configuration of the selected connection
     * @var array
     */
    protected $config;

    /**
     * @var AfasOAuthTokenProvider|null
     */
    protected $oauthTokenProvider;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a getConnector for the selected connection
     * @param string $name
     * @param bool $jsonFilter
     * @return AfasGetConnector
     */
    public function getConnector(string $name, bool $jsonFilter = false, ?string $token = null, ?string $environment = null): AfasGetConnector
    {
        if (!array_key_exists($name, $this->config['getConnectors'])) {
            throw new \InvalidArgumentException("GetConnector $name is not configured for this connection.");
        }

        $config = $this->config['getConnectors'][$name];

        if($token && $environment) {
            $config['token'] = $token;
            $config['environment'] = $environment;
        }

        return new AfasGetConnector($this, $config, $jsonFilter);
    }

    /**
     * Get a updateConnector for the selected connection
     * @param string $name
     * @return AfasUpdateConnector
     */
    public function updateConnector(string $name, ?string $token = null, ?string $environment = null): AfasUpdateConnector
    {
        if (!array_key_exists($name, $this->config['updateConnectors'])) {
            throw new \InvalidArgumentException("UpdateConnector $name is not configured for this connection.");
        }

        $config = $this->config['updateConnectors'][$name];

        if($token && $environment) {
            $config['token'] = $token;
            $config['environment'] = $environment;
        }
        
        return new AfasUpdateConnector($this, $config);
    }

    /**
     * The authentication method for this connection: "token" (classic AfasToken) or "oauth" (client credentials).
     * When not configured explicitly OAuth is used as soon as a client_id is present.
     *
     * @return string
     */
    public function getAuthType(): string
    {
        $auth = $this->config['auth'] ?? null;

        if ($auth) {
            $auth = strtolower($auth);

            if (!in_array($auth, [self::AUTH_TOKEN, self::AUTH_OAUTH])) {
                throw new \InvalidArgumentException("Unknown AFAS auth type \"$auth\". Use \"token\" or \"oauth\".");
            }

            return $auth;
        }

        return $this->getClientId() ? self::AUTH_OAUTH : self::AUTH_TOKEN;
    }

    /**
     * @return bool
     */
    public function usesOAuth(): bool
    {
        return $this->getAuthType() === self::AUTH_OAUTH;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->config['token'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->config['client_id'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->config['client_secret'] ?? null;
    }

    /**
     * The token provider used for OAuth connections. The same instance is reused for the lifetime of the connection.
     *
     * @return AfasOAuthTokenProvider
     */
    public function getOAuthTokenProvider(): AfasOAuthTokenProvider
    {
        if (!$this->oauthTokenProvider) {
            $this->oauthTokenProvider = new AfasOAuthTokenProvider($this, config('afas.oauth', []));
        }

        return $this->oauthTokenProvider;
    }

    /**
     * @param AfasOAuthTokenProvider $provider
     * @return $this
     */
    public function setOAuthTokenProvider(AfasOAuthTokenProvider $provider): AfasConnection
    {
        $this->oauthTokenProvider = $provider;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        if (!$env = $this->config['environment'] ?? null)
        {
            throw new \Exception("No Afas environment set for selected connection.");
        }

        return $env;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTypeOfEnvironment(): string
    {
        switch (strtoupper(substr($this->getEnvironment(), 0, 1)))
        {
            case "O":
                $type = "production";
                break;
            case "T":
                $type = "test";
                break;
            case "A":
                $type = "accept";
                break;
            default:
                throw new \Exception("Could not extract the type of the environment. Please check the environment of the selected connection.");
        }

        return $type;
    }

    public function getEnvironmentNumbers()
    {
        $env = $this->getEnvironment();

        return filter_var($env, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * The base url of the profit rest services for this connection, ending with a slash.
     * Ex: https://11111.resttest.afas.online/profitrestservices/
     *
     * @return string
     * @throws \Exception
     */
    public function getBaseUrl(): string
    {
        $default = ".afas.online/profitrestservices/";

        $url = "https://".$this->getEnvironmentNumbers().".rest";

        switch ($this->getTypeOfEnvironment())
        {
            case 'production':
                return $url.$default;
            case 'test':
                return $url.'test'.$default;
            case 'accept':
                return $url.'accept'.$default;
        }

        throw new \Exception("Could not build the url for the selected connection.");
    }

    /**
     * The OAuth token endpoint for this connection.
     *
     * @return string
     * @throws \Exception
     */
    public function getOAuthTokenUrl(): string
    {
        return $this->getBaseUrl().'oauth/token';
    }
}
