<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Action;
use WArslett\TableBuilder\ActionGroup;

class ActionGroupTest extends TestCase
{
    public function testToString()
    {
        $actionGroup = new ActionGroup([
            'foo' => new Action('Foo'),
            'bar' => new Action('Bar'),
        ]);

        $this->assertSame('foo, bar', (string) $actionGroup);
    }
}
