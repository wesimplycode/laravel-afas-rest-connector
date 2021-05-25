<?php

namespace WeSimplyCode\LaravelAfasRestConnector;

use Illuminate\Http\Client\Response;
use WeSimplyCode\LaravelAfasRestConnector\Interfaces\AfasConnectorInterface;

class AfasUpdateConnector extends AfasConnector implements AfasConnectorInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $meta;

    /**
     * @var array
     */
    public $data;

    public function __construct(AfasConnection $connection, string $name)
    {
        parent::__construct($connection, $name);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $url = $this->buildUrl().'connectors/'.$this->name;

        $metaUrl = $this->buildUrl().$this->meta.'/'.$this->name;

        return $this->meta ? $metaUrl : $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function metaInfo(): Response
    {
        $this->method = 'get';

        $this->meta = 'metainfo/update';

        return $this->execute();
    }

    /**
     * @param array $data
     * @return Response
     * @throws \Exception
     */
    public function insert(array $data): Response
    {
        $this->method = 'POST';

        $this->data = $data;

        return $this->execute();
    }

    public function update()
    {
        $this->method = 'PUT';

        // todo: complete the update method
    }

    public function delete()
    {
        $this->method = 'DELETE';

        // todo: complete the delete method
    }

    // todo: find a way for insert sub record, update main record
}
