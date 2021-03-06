<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasGetConnector
{
    /**
     * The name of the getConnector being used
     * @var string
     */
    protected $name;

    public function __construct(AfasConnection $connection, string $name)
    {
        $this->name = $name;
    }
}
