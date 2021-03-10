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
     * The where filter for the query
     * @var array|null
     */
    protected $where = null;

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
     * @throws \Exception
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
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @return $this
     * @throws \Exception
     */
    public function where(string $filterFieldId, string $operatorType, string $filterValue): AfasGetConnector
    {
        // ToDo: consider making a where function for each filter -> ex: whereIs, whereIsNot (__call())
        if (!$operatorId = $this->getWhereOperatorId($operatorType))
        {
            throw new \Exception("No operatorId found for $operatorType.");
        }

        $filterFieldId == '' ?: $this->where['filterfieldids'][] = $filterFieldId;
        $filterValue == '' ?: $this->where['filtervalues'][] = $filterValue;
        $operatorId == '' ?: $this->where['operatortypes'][] = $operatorId;

        return $this;
    }

    /**
     * @param string $operator
     * @return string|null
     */
    private function getWhereOperatorId(string $operator): ?string
    {
        $afasFilters = config('afas')['filterOperators'];

        for ($i = 1; $i <= count($afasFilters); $i++)
        {
            if (in_array($operator, $afasFilters[$i]))
            {
                return (string) $i;
            }
        }

        return null;
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
     * @throws \Exception
     */
    protected function addFiltersToUrl($url): string
    {
        // ToDo: clean up this code by separating each filter to their own functions
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
                        $i = 1;
                        break;

                    case 'orderByFieldIds':
                        $url .= $i > 0 ? '&'.$filter.'=' : $filter.'=';
                        $i = 1;

                        $amountFields = count($value);
                        $y = 1;

                        foreach ($value as $field)
                        {
                            if (count($value) > 1 && $y < $amountFields)
                            {
                                $url .= $field.urlencode(',');
                            } elseif ($y == $amountFields) {
                                $url .= $field;
                            }

                            $y++;
                        }
                        break;

                    // ToDo: Added a way to add multiple where clauses but do clean up this code (find a better way that is also compatible with AND where)
                    case 'where':
                        $filterFieldIds = 'filterfieldids=';
                        $filterValues = 'filtervalues=';
                        $operatorTypes = 'operatortypes=';

                        for($y = 0; $y <= count($this->where['filterfieldids'])-1; $y++)
                        {
                            if ($y < count($this->where['filterfieldids'])-1)
                            {
                                $filterFieldIds .= $this->where['filterfieldids'][$y].urlencode(',');
                                $filterValues .= $this->where['filtervalues'][$y].urlencode(',');
                                $operatorTypes .= $this->where['operatortypes'][$y].urlencode(',');
                            } elseif ($y == count($this->where['filterfieldids'])-1)
                            {
                                $filterFieldIds .= $this->where['filterfieldids'][$y];
                                $filterValues .= $this->where['filtervalues'][$y];
                                $operatorTypes .= $this->where['operatortypes'][$y];
                            }
                        }

                        $url .= $i > 0 ? '&'.$filterFieldIds. '&' .$filterValues. '&' .$operatorTypes : $filterFieldIds. '&' .$filterValues. '&' .$operatorTypes;
                        $i = 1;
                        break;
                }
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
