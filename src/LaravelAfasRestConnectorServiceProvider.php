<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Support\ServiceProvider;

class LaravelAfasRestConnectorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('Afas', function ($app){
            return new AfasConnectionManager($app['config']->get('afas'));
        });
    }

    public function register()
    {

    }
}
