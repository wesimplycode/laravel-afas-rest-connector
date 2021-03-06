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

    public function connection(string $name = 'default')
    {
        if (!array_key_exists($name, $this->config['connections'])) {
            throw new \InvalidArgumentException("Connection $name is not configured.");
        }

        $config = $this->config['connections'][$name];

        return new AfasConnection($config);
    }

}
