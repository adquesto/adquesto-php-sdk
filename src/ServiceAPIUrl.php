<?php

namespace Adquesto\SDK;

class ServiceAPIUrl
{

    private $serviceId;
    private $baseApiUrl;

    public function __construct($baseApiUrl, $serviceId)
    {
        $this->serviceId = $serviceId;
        $this->baseApiUrl = $baseApiUrl;
    }

    protected function serviceId()
    {
        if (is_callable($this->serviceId)) {
            $serviceId = $this->serviceId;

            return $serviceId();
        }

        return $this->serviceId;
    }

    public function getUrl($endpoint)
    {
        return sprintf('%s%s/%s', $this->baseApiUrl, $this->serviceId(), $endpoint);
    }
}
