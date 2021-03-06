<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Support\Facades\Http;

class AfasClient
{
    /**
     * @var string
     */
    protected $url;

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
     */
    public function buildUrl(): ?string
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

        $url = $this->addCorrectConnectorToUrl($url);

        return $url;
    }

    private function addCorrectConnectorToUrl($url): string
    {
        $connectorType = explode('Afas', explode('\\', get_class($this->connector))[2])[1];

        if ($connectorType == 'GetConnector' || $connectorType == 'UpdateConnector')
        {
            $url .= 'connectors/'.$this->connector->getName();
        }

        // ToDo: add fileconnector, imageconnector etc with elseifs

        if ($connectorType == 'GetConnector')
        {
            $url = $this->connector->addFiltersToUrl($url);
        }

        return $url;
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
        ])->$method($this->url, $data == [] ? null : $data);
    }
}
