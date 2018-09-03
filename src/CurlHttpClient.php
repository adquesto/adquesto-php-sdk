<?php

namespace Adquesto\SDK;

class CurlHttpClient implements HttpClient
{
    protected function init_curl($url, array $headers = array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        return $ch;
    }

    protected function exec_curl($ch, $throwWhenNot200 = false)
    {
        $content = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($errno > 0) {
            throw new NetworkErrorException($error);
        }

        if ($throwWhenNot200 && $response_code != 200) {
            throw new NetworkErrorException('Response code is not 200', $response_code);
        }

        return $content;
    }

    public function get($url, array $headers = array(), $throwWhenNot200 = false)
    {
        $ch = $this->init_curl($url, $headers);
        return $this->exec_curl($ch, $throwWhenNot200);
    }

    public function post($url, array $data = array(), array $headers = array(), $throwWhenNot200 = false)
    {
        $ch = $this->init_curl($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return $this->exec_curl($ch, $throwWhenNot200);
    }
}
