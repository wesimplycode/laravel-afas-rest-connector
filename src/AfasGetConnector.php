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
     * The orWhere filter for the query
     * @var array|null
     */
    protected $orWhere = null;

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
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @return AfasGetConnector
     */
    public function orWhere(string $filterFieldId, string $operatorType, string $filterValue): AfasGetConnector
    {
        if (!$operatorId = $this->getWhereOperatorId($operatorType))
        {
            throw new \Exception("No operatorId found for $operatorType.");
        }

        $filterFieldId == '' ?: $this->orWhere['filterfieldids'][] = $filterFieldId;
        $filterValue == '' ?: $this->orWhere['filtervalues'][] = $filterValue;
        $operatorId == '' ?: $this->orWhere['operatortypes'][] = $operatorId;

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
                        $url = $this->addOrderByFieldIdsFilterToUrl($url, $i);
                        $i = 1;
                        break;
                    case 'where':
                    case 'orWhere':
                        $array = $this->mergeWhereFilters();
                        $url .= $i > 0 ? '&'.$array['filterFieldIds'].'&'.$array['filterValues'].'&'.$array['operatorTypes'] : $array['filterFieldIds'].'&'.$array['filterValues'].'&'.$array['operatorTypes'];
                }
            }
        }

        return $url;
    }

    /**
     * @return array|string[]
     */
    protected function mergeWhereFilters(): array
    {
        $array = [
            'filterFieldIds' => 'filterfieldids=',
            'filterValues' => 'filtervalues=',
            'operatorTypes' => 'operatortypes='
        ];

        if ($this->where && $this->orWhere)
        {
            $array = $this->attachWhereFilters($array, $this->where, ',');
            $array['filterFieldIds'] .= urlencode(';');
            $array['filterFieldIds'] .= urlencode(';');
            $array['filterValues'] .= urlencode(';');
            $array['operatorTypes'] .= urlencode(';');
            $array = $this->attachWhereFilters($array, $this->orWhere, ';');
        } elseif ($this->where)
        {
            $array = $this->attachWhereFilters($array, $this->where, ',');
        } elseif ($this->orWhere)
        {
            $array = $this->attachWhereFilters($array, $this->orWhere, ';');
        }

        return $array;
    }

    /**
     * @param array $array
     * @param array $filter
     * @param string $punctuationMark
     * @return array
     */
    protected function attachWhereFilters(array $array, array $filter, string $punctuationMark): array
    {
        for ($i = 0; $i <= count($filter['filterfieldids'])-1; $i++)
        {
            if ($i < count($filter['filterfieldids'])-1)
            {
                $array['filterFieldIds'] .= $filter['filterfieldids'][$i].urlencode($punctuationMark);
                $array['filterValues'] .= $filter['filtervalues'][$i].urlencode($punctuationMark);
                $array['operatorTypes'] .= $filter['operatortypes'][$i].urlencode($punctuationMark);
            } else {
                $array['filterFieldIds'] .= $filter['filterfieldids'][$i];
                $array['filterValues'] .= $filter['filtervalues'][$i];
                $array['operatorTypes'] .= $filter['operatortypes'][$i];
            }
        }

        return $array;
    }

    /**
     * @param string $url
     * @param int $position
     * @return string
     */
    protected function addOrderByFieldIdsFilterToUrl(string $url, int $position): string
    {
        $url .= $position > 0 ? '&orderbyfieldids=' : 'orderbyfieldids=';

        $amountFields = count($this->orderByFieldIds);
        $i = 1;

        foreach ($this->orderByFieldIds as $field)
        {
            if ($amountFields > 1 && $i < $amountFields)
            {
                $url .= $field.urlencode(',');
            } else {
                $url .= $field;
            }

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
