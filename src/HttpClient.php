<?php

namespace Adquesto\SDK;

interface HttpClient
{
    /**
     * @throws NetworkErrorException
     */
    public function get($url, array $headers = array());

    /**
     * @throws NetworkErrorException
     */
    public function post($url, array $data = array(), array $headers = array());
}
