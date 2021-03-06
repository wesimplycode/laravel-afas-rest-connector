<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasGetConnector extends AfasConnector
{
    public function __construct(AfasConnection $connection, string $name)
    {
        dd($connection->getEnvironment());
        parent::__construct($connection, $name);
    }
}
