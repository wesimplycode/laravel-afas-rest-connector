<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Support\Facades\Http;

class AfasClient
{
    /**
     * The connection to AFAS
     * @var AfasConnection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $url;

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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        switch ($name)
        {
            case 'get':
                $method = 'GET';
                break;
            case 'post':
                $method = 'POST';
                break;
            case 'put':
                $method = 'PUT';
                break;
            case 'delete':
                $method = 'DELETE';
                break;
            default:
                throw new \Exception("Method $name is not allowed.");
        }

        $this->url = $this->buildUrl();

        if (!$this->url)
        {
            throw new \Exception("Error building URL.");
        } else {
            return $this->makeRequest($method, $arguments);
        }
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function buildUrl(): ?string
    {
        if (!$env = $this->connection->getEnvironment())
        {
            throw new \Exception("No Afas environment set for selected connection.");
        }

        return null;
        //todo: build url
    }

    /**
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function makeRequest(string $method, array $data = [])
    {
        if (!$this->connection->getToken())
        {
            throw new \Exception("AFAS token not found.");
        }

        return Http::withHeaders([
            'Authorization' => "AfasToken ".base64_encode($this->connection->getToken())
        ])->$method($this->url, $data);
    }
}
