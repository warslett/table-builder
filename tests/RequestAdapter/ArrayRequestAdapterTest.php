<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RequestAdapter;

use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\Tests\TestCase;

class ArrayRequestAdapterTest extends TestCase
{
    public function testGetParametersReturnsArray()
    {
        $array = ['foo' => 'bar'];

        $requestAdapter = ArrayRequestAdapter::withArray($array);

        $this->assertSame($array, $requestAdapter->getParameters());
    }
}
