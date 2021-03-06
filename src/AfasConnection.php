<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasConnection
{
    /**
     * The configuration of the selected connection
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
