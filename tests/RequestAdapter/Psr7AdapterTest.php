<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RequestAdapter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use WArslett\TableBuilder\RequestAdapter\Psr7Adapter;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class Psr7AdapterTest extends TestCase
{

    public function testGetParameters()
    {
        $queryString = 'table%5Bpage%5D=2&table%5Brows_per_page%5D=2&table%5Bsort_column%5D=name';
        $adapter = Psr7Adapter::withRequest($this->mockRequest($queryString));

        $actual = $adapter->getParameters();

        $this->assertSame([
            'table' => [
                'page' => '2',
                'rows_per_page' => '2',
                'sort_column' => 'name'
            ]
        ], $actual);
    }

    /**
     * @param string $queryString
     * @return RequestInterface&Mock
     */
    private function mockRequest(string $queryString): RequestInterface
    {
        $request = m::mock(RequestInterface::class);
        $uri = m::mock(UriInterface::class);
        $uri->shouldReceive('getQuery')->andReturn($queryString);
        $request->shouldReceive('getUri')->andReturn($uri);
        return $request;
    }
}
