<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;
use WeSimplyCode\LaravelAfasRestConnector\LaravelAfasRestConnectorServiceProvider;

abstract class TestCase extends Orchestra
{
    const TOKEN_URL = 'https://11111.resttest.afas.online/profitrestservices/oauth/token';
    const CLASSIC_TOKEN = '<token><version>1</version><data>abc</data></token>';

    /**
     * Number of token requests received by the fake AFAS
     * @var int
     */
    protected $tokenRequests = 0;

    /**
     * API requests received by the fake AFAS
     * @var Request[]
     */
    protected $apiRequests = [];

    /**
     * Status codes to return for the next API requests (shifted per request, 200 when empty)
     * @var int[]
     */
    protected $nextApiStatuses = [];

    protected function getPackageProviders($app): array
    {
        return [LaravelAfasRestConnectorServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');

        $app['config']->set('afas.connections', [
            'default' => [
                'environment' => 'T11111AA',
                'auth' => null,
                'token' => null,
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
                'getConnectors' => ['contacts' => 'Profit_Contacts'],
                'updateConnectors' => ['persons' => 'KnPerson'],
            ],
            'classic' => [
                'environment' => 'O22222AA',
                'auth' => null,
                'token' => self::CLASSIC_TOKEN,
                'client_id' => null,
                'client_secret' => null,
                'getConnectors' => ['contacts' => 'Profit_Contacts'],
                'updateConnectors' => ['persons' => 'KnPerson'],
            ],
        ]);
    }

    /**
     * Fake AFAS: the token endpoint hands out tok1, tok2, ... and every other url returns rows.
     */
    protected function fakeAfas(): void
    {
        Http::fake(function (Request $request) {
            if (substr($request->url(), -12) === '/oauth/token') {
                $this->tokenRequests++;

                return Http::response([
                    'access_token' => 'tok'.$this->tokenRequests,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'refresh_token' => null,
                ]);
            }

            $this->apiRequests[] = $request;

            $status = array_shift($this->nextApiStatuses) ?: 200;

            return Http::response($status === 200 ? ['rows' => []] : 'error', $status);
        });
    }

    protected function lastApiRequest(): Request
    {
        return end($this->apiRequests);
    }

    protected function authorizationHeader(Request $request): ?string
    {
        return $request->header('Authorization')[0] ?? null;
    }
}
