<?php

namespace WeSimplyCode\LaravelAfasRestConnector\Interfaces;

interface AfasConnectorInterface
{
    /**
     * Get the first part of the url with: $url = $this->buildUrl()
     * @return string
     */
    public function getUrl(): string;

    /**
     * The method that should be used to make the call to the AFAS profitServices
     * @return string
     */
    public function getMethod(): string;
}
