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
        $client = new AfasClient($this->connection, $this);

        if ($this instanceof AfasGetConnector)
        {
            return $client->get();
        } elseif ($this instanceof AfasUpdateConnector)
        {
            // ToDo: implement the put and delete methods
            return $client->post();
        }
    }
}
