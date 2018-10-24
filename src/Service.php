<?php

namespace Adquesto\SDK;

class Service
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var ServiceAPIUrl
     */
    private $serviceAPIUrl;

    public function __construct(ServiceAPIUrl $serviceAPIUrl, HttpClient $httpClient)
    {
        $this->serviceAPIUrl = $serviceAPIUrl;
        $this->httpClient = $httpClient;
    }

    public function fetchStatus()
    {
        $response = $this->httpClient->get(
            $this->serviceAPIUrl->getUrl('status')
        );
        $jsonResponse = json_decode($response, true);
        return $jsonResponse;
    }
}
