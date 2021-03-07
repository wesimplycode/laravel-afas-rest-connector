<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class AfasGetConnector extends AfasConnector
{
    /**
     * The amount of results that should be in the response
     * @var int
     */
    protected $take;

    /**
     * The amount of results that should be skipped in the response
     * @var int
     */
    protected $skip;

    protected $where = array();

    public function __construct(AfasConnection $connection, string $name)
    {
        parent::__construct($connection, $name);
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function take(int $amount): AfasGetConnector
    {
        $this->take = (string) $amount;

        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function skip(int $amount): AfasGetConnector
    {
        $this->skip = (string) $amount;

        return $this;
    }

    /**
     * @param $url
     * @return string
     */
    public function addFiltersToUrl($url): string
    {
        $filters = $this->getFilters();
        $filters = $this->removeEmptyFilters($filters);

        $i = 0;

        $url .= '?';

        foreach ($filters as $filter => $value)
        {
            $url .= $i > 0 ? '&'.$filter.'='.$value : $filter.'='.$value;
            $i++;
        }

        return $url;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function removeEmptyFilters(array $filters): array
    {
        foreach ($filters as $filter => $value)
        {
            if ($value == null || $value == '')
            {
                unset($filters[$filter]);
            }
        }

        return $filters;
    }

    /**
     * @return array
     */
    private function getFilters(): array
    {
        return array_filter(get_object_vars($this), function ($key){
            return !in_array($key, ['name', 'connection']);
        }, ARRAY_FILTER_USE_KEY);
    }
}
