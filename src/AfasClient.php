<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasClient
{
    /**
     * The connection to AFAS
     * @var AfasConnection
     */
    protected $connection;

    /**
     * The selected connector for the connection
     * @var AfasConnector
     */
    protected $connector;

    public function __construct(AfasConnection $connection, AfasConnector $connector)
    {
        $this->connection = $connection;
        $this->connector = $connector;
    }
}
