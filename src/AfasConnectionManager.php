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
     * @param string|null $token
     * @param string|null $environment
     * @return AfasConnection
     */
    public function connection(string $name = 'default', string $token = null, string $environment = null): AfasConnection
    {
        if (!array_key_exists($name, $this->config['connections'])) {
            throw new \InvalidArgumentException("Connection $name is not configured.");
        }

        $config = $this->config['connections'][$name];

        if($token && $environment) {
            $config['token'] = $token;
            $config['environment'] = $environment;
        }

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
    public function getConnector(string $name, bool $jsonFilter = false , string $connection = 'default', string $token = null, string $environment = null): AfasGetConnector
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
