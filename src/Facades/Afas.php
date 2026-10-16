<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasConnection connection(string $name = 'default', ?string $token = null, ?string $environment = null)
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasConnection oauthConnection(string $clientId, string $clientSecret, ?string $environment = null, string $name = 'default')
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasConnection connectionWith(string $name, array $overrides = [])
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasGetConnector getConnector(string $name, bool $jsonFilter = false , string $connection = 'default')
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasUpdateConnector updateConnector(string $name, string $connection = 'default')
 *
 * @see \WeSimplyCode\LaravelAfasRestConnector\AfasConnectionManager
 */
class Afas extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Afas';
    }
}
