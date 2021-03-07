<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Facades;

use Illuminate\Support\Facades\Facade;
use WeSimplyCode\LaravelAfasRestConnector\AfasConnectionManager;

/**
 * @method AfasConnectionManager connection(string $name = 'default')
 * @method AfasConnectionManager getConnector(string $name, string $connection = 'default')
 */
class Afas extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Afas';
    }
}
