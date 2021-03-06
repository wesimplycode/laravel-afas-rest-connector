<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Http\Client\Response;

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

    /**
     * @return Response
     * @throws \Exception
     */
    public function execute(): Response
    {
        $client = new AfasClient($this->connection, $this);

        $method = strtolower($this->getMethod());

        return $method != 'get' ? $client->makeRequest($method, $this->data):$client->makeRequest($method);
    }

    /**
     * @return string|null
     */
    protected function buildUrl(): ?string
    {
        $default = ".afas.online/profitrestservices/";

        $url = "https://".$this->connection->getEnvironmentNumbers().".rest";

        if ($this->connection->getTypeOfEnvironment() == 'production')
        {
            $url .= $default;
        } elseif ($this->connection->getTypeOfEnvironment() == 'test')
        {
            $url .= 'test'.$default;
        } elseif ($this->connection->getTypeOfEnvironment() == 'accept')
        {
            $url .= 'accept'.$default;
        } else {
            return null;
        }

        return $url;
    }
}
