<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasClient
{
    /**
     * The connection to AFAS
     * @var AfasConnection
     */
    protected $connection;

    public function __construct(AfasConnection $connection)
    {
        $this->connection = $connection;
    }
}
