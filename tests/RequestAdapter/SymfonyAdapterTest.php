<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RequestAdapter;

use Symfony\Component\HttpFoundation\Request;
use WArslett\TableBuilder\RequestAdapter\SymfonyAdapter;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class SymfonyAdapterTest extends TestCase
{

    public function testGetParameterDelegatesToRequest()
    {
        $request = $this->mockRequest();
        $adapter = SymfonyAdapter::withRequest($request);
        $parameter = 'foo';

        $adapter->getParameter($parameter);

        $request->shouldHaveReceived('get')->once()->with($parameter);
    }

    /**
     * @param array $data
     * @return Request&Mock
     */
    private function mockRequest(array $data = []): Request
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('get')->andReturn($data);
        return $request;
    }
}
