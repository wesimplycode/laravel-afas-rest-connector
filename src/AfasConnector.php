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

        $this->client = new AfasClient($connection, $this);
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
