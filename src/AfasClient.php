<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use WeSimplyCode\LaravelAfasRestConnector\Exceptions\AfasAuthenticationException;

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
     * @return Response
     * @throws \Exception
     */
    public function makeRequest(string $method, array $data = []): Response
    {
        $url = $this->connector->getUrl();
        $body = $data == [] ? null : $data;

        if (!$this->connection->usesOAuth()) {
            if (!$this->connection->getToken()) {
                throw new AfasAuthenticationException("AFAS token not found.");
            }

            return $this->request("AfasToken ".base64_encode($this->connection->getToken()))
                ->$method($url, $body);
        }

        $provider = $this->connection->getOAuthTokenProvider();

        $response = $this->request("Bearer ".$provider->getAccessToken())->$method($url, $body);

        // The cached token may have been revoked or expired early: fetch a fresh one and retry once.
        if ($response->status() === 401) {
            $provider->forgetAccessToken();

            $response = $this->request("Bearer ".$provider->requestAccessToken())->$method($url, $body);
        }

        return $response;
    }

    /**
     * @param string $authorization
     * @return PendingRequest
     */
    protected function request(string $authorization): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $authorization,
        ]);
    }
}
