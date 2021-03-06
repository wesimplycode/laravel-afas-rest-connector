<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasGetConnector extends AfasConnector
{
    /**
     * The amount of results that should be in the response
     * @var int
     */
    protected $take;

    public function __construct(AfasConnection $connection, string $name)
    {
        parent::__construct($connection, $name);
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function take(int $amount)
    {
        $this->take = (string) $amount;

        return $this;
    }

    /**
     * @param $url
     * @return string
     */
    public function addFiltersToUrl($url): string
    {
        $url .= '?';

        if ($this->take)
        {
            $url .= 'take='.$this->take;
        }

        return $url;
    }
}
