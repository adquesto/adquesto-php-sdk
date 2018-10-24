<?php

namespace Adquesto\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Adquesto\SDK\Subscriber;

class SubscriberTest extends TestCase
{
    public function testHasValidSubscription()
    {
        $pastDateTime = new \DateTime;
        $pastDateTime->modify('+1 day');
        $instance = new Subscriber('uuid','test@test.com', $pastDateTime, false);
        $this->assertTrue($instance->isSubscriptionValid());
    }

    public function testSubscriptionExpired()
    {
        $pastDateTime = new \DateTime;
        $pastDateTime->modify('-1 minutes');
        $instance = new Subscriber('uuid', 'test@test.com', $pastDateTime, false);
        $this->assertFalse($instance->isSubscriptionValid());
    }
}
