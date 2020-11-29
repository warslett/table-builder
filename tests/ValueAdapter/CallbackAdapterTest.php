<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\ValueAdapter;

use WArslett\TableBuilder\ValueAdapter\CallbackAdapter;
use WArslett\TableBuilder\Tests\TestCase;

class CallbackAdapterTest extends TestCase
{
    public function testGetValueGetsValueFromCallback()
    {
        $value = 'foo';
        $adapter = CallbackAdapter::withCallback(fn($row) => $value);

        $actual = $adapter->getValue(['bar' => 'baz']);

        $this->assertSame($value, $actual);
    }
}
