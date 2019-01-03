<?php

namespace Adquesto\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Adquesto\SDK\OAuth2Client;
use Adquesto\SDK\SubscriberManager;
use Adquesto\SDK\SubscriberStorage;
use Adquesto\SDK\Subscriber;

class SubscriberManagerTest extends TestCase
{
    public function testHandleRedirectReturnValidSubscriber()
    {
        $oauthClient = $this->createMock(OAuth2Client::class);
        $oauthClient->method('accessToken')->willReturn('access_token_value');
        $oauthClient->method('me')->willReturn([
                'uid' => 1,
                'email' => 'test@email.com',
                'subscriptionDate' => '2018-01-01 00:01:00',
                'recurringPayments' => false,
            ]);
        $storage = $this->createMock(SubscriberStorage::class);

        $instance = new SubscriberManager($oauthClient, $storage);
        $subscriber = $instance->handleRedirect('token');
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertEquals($subscriber->email, 'test@email.com');
    }
}
