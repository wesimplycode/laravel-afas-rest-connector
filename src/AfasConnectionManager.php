<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasConnectionManager
{
    /**
     * The AFAS configuration
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     * @param string|null $token Override the classic token of the configured connection
     * @param string|null $environment Override the environment of the configured connection
     * @return AfasConnection
     */
    public function connection(string $name = 'default', ?string $token = null, ?string $environment = null): AfasConnection
    {
        $overrides = [];

        if($token && $environment) {
            $overrides['token'] = $token;
            $overrides['environment'] = $environment;
            $overrides['auth'] = AfasConnection::AUTH_TOKEN;
        }

        return $this->connectionWith($name, $overrides);
    }

    /**
     * Get a connection that uses the OAuth client credentials flow with the given credentials.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $environment Override the environment of the configured connection
     * @param string $name
     * @return AfasConnection
     */
    public function oauthConnection(string $clientId, string $clientSecret, ?string $environment = null, string $name = 'default'): AfasConnection
    {
        $overrides = [
            'auth' => AfasConnection::AUTH_OAUTH,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        if ($environment) {
            $overrides['environment'] = $environment;
        }

        return $this->connectionWith($name, $overrides);
    }

    /**
     * Get a configured connection with some of its configuration values replaced.
     *
     * @param string $name
     * @param array $overrides Keys: environment, auth, token, client_id, client_secret
     * @return AfasConnection
     */
    public function connectionWith(string $name, array $overrides = []): AfasConnection
    {
        if (!array_key_exists($name, $this->config['connections'])) {
            throw new \InvalidArgumentException("Connection $name is not configured.");
        }

        $config = array_merge($this->config['connections'][$name], $overrides);

        return new AfasConnection($config);
    }

    /**
     * @param string $name
     * @param bool $jsonFilter
     * @param string $connection
     * @param string|null $token
     * @param string|null $environment
     * @return AfasGetConnector
     */
    public function getConnector(string $name, bool $jsonFilter = false , string $connection = 'default', ?string $token = null, ?string $environment = null): AfasGetConnector
    {
        return $this->connection($connection, $token, $environment)->getConnector($name, $jsonFilter);
    }

    /**
     * @param string $name
     * @param string $connection
     * @return AfasUpdateConnector
     */
    public function updateConnector(string $name, string $connection = 'default'): AfasUpdateConnector
    {
        return $this->connection($connection)->updateConnector($name);
    }

}
