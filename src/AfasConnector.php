<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasConnector
{
    /**
     * The name of the connector being used
     * @var string
     */
    protected $name;

    /**
     * The connection to AFAS
     * @var AfasConnection
     */
    protected $connection;

    public function __construct(AfasConnection $connection, string $name)
    {
        $this->name = $name;

        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function execute()
    {
        if ($this instanceof AfasGetConnector)
        {
            return $this->client->get();
        } elseif ($this instanceof AfasUpdateConnector)
        {
            // ToDo: implement the put and delete methods
            return $this->client->post();
        }
    }
}
