<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Support\Facades\Facade;

class AfasFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Afas';
    }
}
