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
     * @var AfasConnector
     */
    protected $connector;

    public function __construct(AfasConnection $connection, AfasConnector $connector)
    {
        $this->connection = $connection;
        $this->connector = $connector;
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
        ])->$method($this->connector->getUrl(), $data == [] ? null : $data);
    }
}
