<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasConnection connection(string $name = 'default')
 * @method static \WeSimplyCode\LaravelAfasRestConnector\AfasGetConnector getConnector(string $name, bool $jsonFilter = false , string $connection = 'default')
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
