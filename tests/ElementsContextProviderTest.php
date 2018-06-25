<?php

namespace Adquesto\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Adquesto\SDK\ElementsContextProvider;

class ElementsContextProviderTest extends TestCase
{
    function testSetsRandomIds()
    {
        $instance = new ElementsContextProvider();
        $this->assertNotNull($instance->mainQuestId());
        $this->assertNotNull($instance->reminderQuestId());
    }
}
