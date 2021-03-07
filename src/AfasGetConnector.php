<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use WeSimplyCode\LaravelAfasRestConnector\Interfaces\AfasConnectorInterface;

class AfasGetConnector extends AfasConnector implements AfasConnectorInterface
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

    /**
     * @var array
     */
    protected $orderByFieldIds = array();

    public function __construct(AfasConnection $connection, string $name)
    {
        parent::__construct($connection, $name);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $url = $this->buildUrl();

        $url .= 'connectors/'.$this->name;

        $url = $this->addFiltersToUrl($url);

        return $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'GET';
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
     * @param string $field
     * @param bool $desc
     * @return AfasGetConnector
     */
    public function sortOnField(string $field, bool $desc = false): AfasGetConnector
    {
        if ($desc)
        {
            array_push($this->orderByFieldIds, '-'.$field);
        } else {
            array_push($this->orderByFieldIds, $field);
        }

        return $this;
    }

    /**
     * @param $url
     * @return string
     */
    protected function addFiltersToUrl($url): string
    {
        $filters = $this->getFilters();
        $filters = $this->removeEmptyFilters($filters);

        if (count($filters) > 0)
        {
            $i = 0;

            $url .= '?';

            foreach ($filters as $filter => $value)
            {
                switch ($filter)
                {
                    case 'skip':
                    case 'take':
                    $url .= $i > 0 ? '&'.$filter.'='.$value : $filter.'='.$value;
                        break;
                    case 'orderByFieldIds':
                        $url .= $i > 0 ? '&'.$filter.'=' : $filter.'=';
                        $amountFields = count($value);
                        $i = 1;

                        foreach ($value as $field)
                        {
                            if (count($value) > 1 && $i < $amountFields)
                            {
                                $url .= $field.urlencode(',');
                            } elseif ($i == $amountFields) {
                                $url .= $field;
                            }

                            $i++;
                        }
                }

                $i++;
            }
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
