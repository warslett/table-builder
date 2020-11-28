<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RouteGeneratorAdapter;

use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;
use WArslett\TableBuilder\Tests\TestCase;

class SprintfAdapterTest extends TestCase
{

    public function testRenderRoute()
    {
        $adapter = new SprintfAdapter();

        $url = $adapter->renderRoute('/foo/%s/bar/%d', ['baz', 123]);

        $this->assertSame('/foo/baz/bar/123', $url);
    }
}
