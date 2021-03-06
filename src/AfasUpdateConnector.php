<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasUpdateConnector extends AfasConnector
{
    public function __construct(AfasConnection $connection, string $name)
    {
        parent::__construct($connection, $name);
    }
}
