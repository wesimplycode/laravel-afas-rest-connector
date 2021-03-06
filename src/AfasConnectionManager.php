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
     * @return AfasConnection
     */
    public function connection(string $name = 'default'): AfasConnection
    {
        if (!array_key_exists($name, $this->config['connections'])) {
            throw new \InvalidArgumentException("Connection $name is not configured.");
        }

        $config = $this->config['connections'][$name];

        return new AfasConnection($config);
    }

    /**
     * @param string $name
     * @param string $connection
     * @return AfasGetConnector
     */
    public function getConnector(string $name, string $connection = 'default'): AfasGetConnector
    {
        return $this->connection($connection)->getConnector($name);
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
