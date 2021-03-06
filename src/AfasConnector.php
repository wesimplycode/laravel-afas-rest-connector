<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasConnector
{
    /**
     * The name of the getConnector being used
     * @var string
     */
    protected $name;

    /**
     * @var AfasClient
     */
    protected $client;

    public function __construct(AfasConnection $connection, string $name)
    {
        $this->name = $name;

        $this->client = new AfasClient($connection);
    }
}
