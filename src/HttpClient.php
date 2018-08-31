<?php

namespace Adquesto\SDK;

interface HttpClient
{
    /**
     * @param $url
     * @param array $headers
     * @param bool $throwWhenNot200
     * @return string
     */
    public function get($url, array $headers = array(), $throwWhenNot200 = false);

    /**
     * @param $url
     * @param array $data
     * @param array $headers
     * @param bool $throwWhenNot200
     * @return string
     */
    public function post($url, array $data = array(), array $headers = array(), $throwWhenNot200 = false);
}
