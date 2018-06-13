<?php

namespace Adquesto\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Adquesto\SDK\HttpClient;
use Adquesto\SDK\OAuth2Client;

class OAuth2ClientTest extends TestCase
{
    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage unit-test-error
     */
    public function testAccessTokenFailsWhenError()
    {
        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->method('post')->willReturn(json_encode([
            'error' => 'unit-test-error',
        ]));
        $instance = new OAuth2Client(
            $httpClient, 'client-id-0', 'http://auth-url', 'http://token-url', 'http://me-url', 'http://redirect-url'
        );
        $instance->accessToken('code');
    }
}
