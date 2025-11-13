<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

class Filter
{
    /**
     * The where filter for the query
     * @var array
     */
    public $where = [
        "Filters" => [
            "Filter" => []
        ]
    ];

    /**
     * @var int
     */
    protected $orWhere = 0;

    /**
     * The amount of results that should be skipped in the response
     * @var string
     */
    protected $skip;

    /**
     * The amount of results that should be in the response
     * @var string
     */
    protected $take;

    /**
     * @var array
     */
    protected $orderByFieldIds = array();

    /**
     * @var bool
     */
    protected $jsonFilter;

    public function __construct(bool $jsonFilter = false)
    {
        $this->jsonFilter = $jsonFilter;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        $filters = $this->removeEmptyFilters();

        $url = '';

        if (count($filters) > 0)
        {
            $i = 0;

            foreach ($filters as $filter => $value)
            {
                switch ($filter)
                {
                    case 'skip':
                    case 'take':
                        $url .= $i > 0 ? '&'.$filter.'='.$value : '?'.$filter.'='.$value;
                        $i = 1;
                        break;
                    case 'orderByFieldIds':
                        $url = $this->addOrderByFieldIdsFilterToUrl($url, $i);
                        $i = 1;
                        break;
                    case 'where':
                        $url = $this->jsonFilter ? $this->buildJsonWhereClause() : $this->buildSimpleWhereClause();
                        $i = 1;
                        break;
                }
            }
        }

        return $url;
    }

    /**
     * @return array
     */
    public function getWhere(): array
    {
        return $this->where;
    }

    public function setWhere(array $whereFilter)
    {
        $this->where = $whereFilter;
    }

    /**
     * @param int $amount
     */
    public function setTake(int $amount): void
    {
        $this->take = (string) $amount;
    }

    /**
     * @param int $amount
     */
    public function setSkip(int $amount): void
    {
        $this->skip = (string) $amount;
    }

    /**
     * @param string $field
     * @param bool $desc
     */
    public function sortOnField(string $field, bool $desc): void
    {
        if ($desc)
        {
            array_push($this->orderByFieldIds, '-'.$field);
        } else {
            array_push($this->orderByFieldIds, $field);
        }
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @throws \Exception
     */
    public function addToCurrentWhereClause(string $filterFieldId, string $operatorType, string $filterValue): void
    {
        if (!$operatorId = $this->getWhereOperatorId($operatorType))
        {
            throw new \Exception("No operatorId found for $operatorType.");
        }

        if (array_key_exists(0, $this->where['Filters']['Filter']))
        {
            $this->pushNewFieldToWhereFilter($filterFieldId, $operatorId, $filterValue, $this->orWhere);
        } else {
            $this->pushNewWhereFilter($filterFieldId, $operatorId, $filterValue);
        }
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @throws \Exception
     */
    public function addToNewWhereClause(string $filterFieldId, string $operatorType, string $filterValue): void
    {
        if (!$operatorId = $this->getWhereOperatorId($operatorType))
        {
            throw new \Exception("No operatorId found for $operatorType.");
        }

        $this->orWhere += 1;

        if (array_key_exists($this->orWhere, $this->where['Filters']['Filter']))
        {
            $this->pushNewFieldToWhereFilter($filterFieldId, $operatorId, $filterValue, $this->orWhere);
        } else {
            $this->pushNewWhereFilter($filterFieldId, $operatorId, $filterValue);
        }
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorId
     * @param string $filterValue
     */
    private function pushNewWhereFilter(string $filterFieldId, string $operatorId, string $filterValue): void
    {
        array_push($this->where['Filters']['Filter'], [
            "@FilterId" => "Filter " . (count($this->where['Filters']['Filter']) + 1),
            "Field" => [
                [
                    "@FieldId" => "$filterFieldId",
                    "@OperatorType" => "$operatorId",
                    "#text" => "$filterValue",
                ]
            ]
        ]);
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorId
     * @param string $filterValue
     * @param int $whereFilterKey
     */
    private function pushNewFieldToWhereFilter(string $filterFieldId, string $operatorId, string $filterValue, int $whereFilterKey): void
    {
        array_push($this->where['Filters']['Filter'][$whereFilterKey]['Field'], [
            "@FieldId" => "$filterFieldId",
            "@OperatorType" => "$operatorId",
            "#text" => "$filterValue",
        ]);
    }

    /**
     * @param string $operator
     * @return string|null
     */
    public function getWhereOperatorId(string $operator): ?string
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
     * @param string $url
     * @param int $position
     * @return string
     */
    protected function addOrderByFieldIdsFilterToUrl(string $url, int $position): string
    {
        $url .= $position > 0 ? '&orderbyfieldids=' : '?orderbyfieldids=';

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
     * @return string
     */
    protected function buildSimpleWhereClause(): string
    {
        $array = [
            'filterFieldIds' => 'filterfieldids=',
            'filterValues' => 'filtervalues=',
            'operatorTypes' => 'operatortypes='
        ];

        for ($i = 0; $i < count($this->where['Filters']['Filter']); $i++)
        {
            for ($y = 0; $y < count($this->where['Filters']['Filter'][$i]['Field']); $y++)
            {
                $array['filterFieldIds'] .= $this->where['Filters']['Filter'][$i]['Field'][$y]['@FieldId'];
                $array['operatorTypes'] .= $this->where['Filters']['Filter'][$i]['Field'][$y]['@OperatorType'];
                $array['filterValues'] .= $this->where['Filters']['Filter'][$i]['Field'][$y]['#text'];

                if ($y < count($this->where['Filters']['Filter'][$i]['Field'])-1)
                {
                    $array['filterFieldIds'] .= rawurlencode(',');
                    $array['filterValues'] .= rawurlencode(',');
                    $array['operatorTypes'] .= rawurlencode(',');
                }
            }

            if ($i < count($this->where['Filters']['Filter'])-1)
            {
                $array['filterFieldIds'] .= rawurlencode(';');
                $array['filterValues'] .= rawurlencode(';');
                $array['operatorTypes'] .= rawurlencode(';');
            }
        }

        return '?'.$array['filterFieldIds'].'&'.$array['filterValues'].'&'.$array['operatorTypes'];
    }

    /**
     * @return string
     */
    protected function buildJsonWhereClause(): string
    {
        return '?filterjson='.rawurlencode(json_encode($this->where));
    }

    /**
     * @return array
     */
    private function removeEmptyFilters(): array
    {
        $filters = get_object_vars($this);

        foreach ($filters as $filter => $value)
        {
            if ($value == null || $value == '' || $filter == 'jsonFilter')
            {
                unset($filters[$filter]);
            }
        }

        if (count($this->where['Filters']['Filter']) === 0)
        {
            unset($filters['where']);
        }

        return $filters;
    }
}
