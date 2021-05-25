<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Http\Client\Response;
use WeSimplyCode\LaravelAfasRestConnector\Interfaces\AfasConnectorInterface;

class AfasGetConnector extends AfasConnector implements AfasConnectorInterface
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var string
     */
    private $meta;

    public function __construct(AfasConnection $connection, string $name, bool $jsonFilter = false)
    {
        parent::__construct($connection, $name);

        $this->filter = new Filter($jsonFilter);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getUrl(): string
    {
        $url = $this->buildUrl();

        $url .= 'connectors/'.$this->name;

        $url .= $this->filter->url();

        $metaUrl = $this->buildUrl().$this->meta.'/'.$this->name;

        return $this->meta ? $metaUrl : $url;
    }

    /**
     * @return string
     */
    public function getJsonFilter(): string
    {
        return json_encode($this->filter->getWhere());
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'GET';
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function metaInfo(): Response
    {
        $this->meta = 'metainfo/get';

        return $this->execute();
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function take(int $amount): AfasGetConnector
    {
        $this->filter->setTake($amount);

        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function skip(int $amount): AfasGetConnector
    {
        $this->filter->setSkip($amount);

        return $this;
    }

    /**
     * @param string $field
     * @param bool $desc
     * @return AfasGetConnector
     */
    public function sortOnField(string $field, bool $desc = false): AfasGetConnector
    {
        $this->filter->sortOnField($field, $desc);

        return $this;
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @return AfasGetConnector
     * @throws \Exception
     */
    public function where(string $filterFieldId, string $operatorType, string $filterValue): AfasGetConnector
    {
        $this->filter->addToCurrentWhereClause($filterFieldId, $operatorType, $filterValue);

        return $this;
    }

    /**
     * @param string $filterFieldId
     * @param string $operatorType
     * @param string $filterValue
     * @return $this
     * @throws \Exception
     */
    public function orWhere(string $filterFieldId, string $operatorType, string $filterValue): AfasGetConnector
    {
        $this->filter->addToNewWhereClause($filterFieldId, $operatorType, $filterValue);

        return $this;
    }
}
