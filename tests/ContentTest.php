<?php

namespace Adquesto\SDK\Tests;

use PHPUnit\Framework\TestCase;

use Adquesto\SDK\Content;
use Adquesto\SDK\CurlHttpClient;
use Adquesto\SDK\InMemoryStorage;
use Adquesto\SDK\PositioningSettings;


class ContentTest extends TestCase
{
    function testGetChildNodesFromContent()
    {
        $html = '<div class="header"><h1>HEADER</h1></div><div class="p2">2</div><div class="p2">P2</div>';
        $instance = new Content('', 1, new InMemoryStorage(), new CurlHttpClient(),
        						PositioningSettings::factory(PositioningSettings::STRATEGY_UPPER));
        $nodes = $instance->getChildNodesFromContent($html);
        $this->assertEquals(count($nodes), 3);
        $this->assertInstanceOf('\simple_html_dom\simple_html_dom_node', $nodes[0]);
    }
}
